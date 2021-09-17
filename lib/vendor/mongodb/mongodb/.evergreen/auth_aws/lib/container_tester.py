#!/usr/bin/env python3
"""
Script for testing mongodb in containers.

Requires ssh, scp, and sh on local and remote hosts.
Assumes remote host is Linux
"""

import argparse
import datetime
import logging
import os
import pprint
import subprocess
import uuid

import boto3

LOGGER = logging.getLogger(__name__)


############################################################################
# Default configuration settings for working with a ECS cluster in a region
#

# These settings depend on a cluster, task subnets, and security group already setup
ECS_DEFAULT_CLUSTER = "arn:aws:ecs:us-east-2:579766882180:cluster/tf-mcb-ecs-cluster"
ECS_DEFAULT_TASK_DEFINITION = "arn:aws:ecs:us-east-2:579766882180:task-definition/tf-app:2"
ECS_DEFAULT_SUBNETS = ['subnet-a5e114cc']
# Must allow ssh from 0.0.0.0
ECS_DEFAULT_SECURITY_GROUP = 'sg-051a91d96332f8f3a'

# This is just a string local to this file
DEFAULT_SERVICE_NAME = 'script-test'

# Garbage collection threshold for old/stale services
DEFAULT_GARBAGE_COLLECTION_THRESHOLD = datetime.timedelta(hours=1)

############################################################################


def _run_process(params, cwd=None):
    LOGGER.info("RUNNING COMMAND: %s", params)
    ret = subprocess.run(params, cwd=cwd)
    return ret.returncode

def _userandhostandport(endpoint):
    user_and_host = endpoint.find("@")
    if user_and_host == -1:
        raise ValueError("Invalid endpoint, Endpoint must be user@host:port")
    (user, host) = (endpoint[:user_and_host], endpoint[user_and_host + 1:])

    colon = host.find(":")
    if colon == -1:
        return (user, host, "22")
    return (user, host[:colon], host[colon + 1:])

def _scp(endpoint, src, dest):
    (user, host, port) = _userandhostandport(endpoint)
    cmd = ["scp", "-o", "StrictHostKeyChecking=no", "-P", port, src, "%s@%s:%s" % (user, host, dest)]
    if os.path.isdir(src):
       cmd.insert(5, "-r")
    _run_process(cmd)

def _ssh(endpoint, cmd):
    (user, host, port) = _userandhostandport(endpoint)
    cmd = ["ssh", "-o", "StrictHostKeyChecking=no", "-p", port, "%s@%s" % (user, host), cmd ]
    ret = _run_process(cmd)
    LOGGER.info("RETURN CODE: %s", ret)
    return ret

def _run_test_args(args):
    run_test(args.endpoint, args.script, args.files)

def run_test(endpoint, script, files):
    """
    Run a test on a machine

    Steps
    1. Copy over a files which are tuples of (src, dest)
    2. Copy over the test script to "/tmp/test.sh"
    3. Run the test script and return the results
    """
    LOGGER.info("Copying files to %s", endpoint)

    for file in files:
        colon = file.find(":")
        (src, dest) = (file[:colon], file[colon + 1:])
        _scp(endpoint, src, dest)

    LOGGER.info("Copying script to %s", endpoint)
    _scp(endpoint, script, "/tmp/test.sh")
    return_code = _ssh(endpoint, "/bin/bash -x /tmp/test.sh")
    if return_code != 0:
        LOGGER.error("FAILED: %s", return_code)
        raise ValueError(f"test failed with {return_code}")

def _get_region(arn):
    return arn.split(':')[3]


def _remote_ps_container_args(args):
    remote_ps_container(args.cluster)

def remote_ps_container(cluster):
    """
    Get a list of task running in the cluster with their network addresses.

    Emulates the docker ps and ecs-cli ps commands.
    """
    ecs_client = boto3.client('ecs', region_name=_get_region(cluster))
    ec2_client = boto3.client('ec2', region_name=_get_region(cluster))

    tasks = ecs_client.list_tasks(cluster=cluster)

    task_list = ecs_client.describe_tasks(cluster=cluster, tasks=tasks['taskArns'])

    #Example from ecs-cli tool
    #Name                                       State    Ports                    TaskDefinition  Health
    #aa2c2642-3013-4370-885e-8b8d956e753d/sshd  RUNNING  3.15.149.114:22->22/tcp  sshd:1          UNKNOWN

    print("Name                                       State    Public IP                Private IP               TaskDefinition  Health")
    for task in task_list['tasks']:

        taskDefinition = task['taskDefinitionArn']
        taskDefinition_short = taskDefinition[taskDefinition.rfind('/') + 1:]

        private_ip_address = None
        enis = []
        for b in [ a['details'] for a in task["attachments"] if a['type'] == 'ElasticNetworkInterface']:
            for c in b:
                if c['name'] == 'networkInterfaceId':
                    enis.append(c['value'])
                elif c['name'] == 'privateIPv4Address':
                    private_ip_address = c['value']
        assert enis
        assert private_ip_address

        eni = ec2_client.describe_network_interfaces(NetworkInterfaceIds=enis)
        public_ip = [n["Association"]["PublicIp"] for n in eni["NetworkInterfaces"]][0]

        for container in task['containers']:
            taskArn = container['taskArn']
            task_id = taskArn[taskArn.rfind('/')+ 1:]
            name = container['name']
            task_id = task_id + "/" + name
            lastStatus = container['lastStatus']

        print("{:<43}{:<9}{:<25}{:<25}{:<16}".format(task_id, lastStatus, public_ip, private_ip_address, taskDefinition_short ))

def _remote_create_container_args(args):
    remote_create_container(args.cluster, args.task_definition, args.service, args.subnets, args.security_group)

def remote_create_container(cluster, task_definition, service_name, subnets, security_group):
    """
    Create a task in ECS
    """
    ecs_client = boto3.client('ecs', region_name=_get_region(cluster))

    resp = ecs_client.create_service(cluster=cluster, serviceName=service_name,
        taskDefinition = task_definition,
        desiredCount = 1,
        launchType='FARGATE',
        networkConfiguration={
            'awsvpcConfiguration': {
                'subnets': subnets,
                'securityGroups': [
                    security_group,
                ],
                'assignPublicIp': "ENABLED"
            }
        }
        )

    pprint.pprint(resp)

    service_arn = resp["service"]["serviceArn"]
    print(f"Waiting for Service {service_arn} to become active...")

    waiter = ecs_client.get_waiter('services_stable')

    waiter.wait(cluster=cluster, services=[service_arn])

def _remote_stop_container_args(args):
    remote_stop_container(args.cluster, args.service)

def remote_stop_container(cluster, service_name):
    """
    Stop a ECS task
    """
    ecs_client = boto3.client('ecs', region_name=_get_region(cluster))

    resp = ecs_client.delete_service(cluster=cluster, service=service_name, force=True)
    pprint.pprint(resp)

    service_arn = resp["service"]["serviceArn"]

    print(f"Waiting for Service {service_arn} to become inactive...")
    waiter = ecs_client.get_waiter('services_inactive')

    waiter.wait(cluster=cluster, services=[service_arn])

def _remote_gc_services_container_args(args):
    remote_gc_services_container(args.cluster)

def remote_gc_services_container(cluster):
    """
    Delete all ECS services over then a given treshold.
    """
    ecs_client = boto3.client('ecs', region_name=_get_region(cluster))

    services = ecs_client.list_services(cluster=cluster)
    if not services["serviceArns"]:
        return

    services_details = ecs_client.describe_services(cluster=cluster, services=services["serviceArns"])

    not_expired_now = datetime.datetime.now().astimezone() - DEFAULT_GARBAGE_COLLECTION_THRESHOLD

    for service in services_details["services"]:
        created_at = service["createdAt"]

        # Find the services that we created "too" long ago
        if created_at < not_expired_now:
            print("DELETING expired service %s which was created at %s." % (service["serviceName"], created_at))

            remote_stop_container(cluster, service["serviceName"])

def remote_get_public_endpoint_str(cluster, service_name):
    """
    Get an SSH connection string for the remote service via the public ip address
    """
    ecs_client = boto3.client('ecs', region_name=_get_region(cluster))
    ec2_client = boto3.client('ec2', region_name=_get_region(cluster))

    tasks = ecs_client.list_tasks(cluster=cluster, serviceName=service_name)

    task_list = ecs_client.describe_tasks(cluster=cluster, tasks=tasks['taskArns'])

    for task in task_list['tasks']:

        enis = []
        for b in [ a['details'] for a in task["attachments"] if a['type'] == 'ElasticNetworkInterface']:
            for c in b:
                if c['name'] == 'networkInterfaceId':
                    enis.append(c['value'])
        assert enis

        eni = ec2_client.describe_network_interfaces(NetworkInterfaceIds=enis)
        public_ip = [n["Association"]["PublicIp"] for n in eni["NetworkInterfaces"]][0]
        break

    return f"root@{public_ip}:22"

def remote_get_endpoint_str(cluster, service_name):
    """
    Get an SSH connection string for the remote service via the private ip address
    """
    ecs_client = boto3.client('ecs', region_name=_get_region(cluster))

    tasks = ecs_client.list_tasks(cluster=cluster, serviceName=service_name)

    task_list = ecs_client.describe_tasks(cluster=cluster, tasks=tasks['taskArns'])

    for task in task_list['tasks']:

        private_ip_address = None
        for b in [ a['details'] for a in task["attachments"] if a['type'] == 'ElasticNetworkInterface']:
            for c in b:
                if c['name'] == 'privateIPv4Address':
                    private_ip_address = c['value']
        assert private_ip_address
        break

    return f"root@{private_ip_address}:22"

def _remote_get_endpoint_args(args):
    _remote_get_endpoint(args.cluster, args.service)

def _remote_get_endpoint(cluster, service_name):
    endpoint = remote_get_endpoint_str(cluster, service_name)
    print(endpoint)

def _get_caller_identity(args):
    sts_client = boto3.client('sts')

    pprint.pprint(sts_client.get_caller_identity())


def _run_e2e_test_args(args):
    _run_e2e_test(args.script, args.files, args.cluster, args.task_definition, args.subnets, args.security_group)

def _run_e2e_test(script, files, cluster, task_definition, subnets, security_group):
    """
    Run a test end-to-end

    1. Start an ECS service
    2. Copy the files over and run the test
    3. Stop the ECS service
    """
    service_name = str(uuid.uuid4())

    remote_create_container(cluster, task_definition, service_name, subnets, security_group)

    # The build account hosted ECS tasks are only available via the private ip address
    endpoint = remote_get_endpoint_str(cluster, service_name)
    if cluster == ECS_DEFAULT_CLUSTER:
        # The test account hosted ECS tasks are the opposite, only public ip address access
        endpoint = remote_get_public_endpoint_str(cluster, service_name)

    try:
        run_test(endpoint, script, files)
    finally:
        remote_stop_container(cluster, service_name)


def main() -> None:
    """Execute Main entry point."""

    parser = argparse.ArgumentParser(description='ECS container tester.')

    parser.add_argument('-v', "--verbose", action='store_true', help="Enable verbose logging")
    parser.add_argument('-d', "--debug", action='store_true', help="Enable debug logging")

    sub = parser.add_subparsers(title="Container Tester subcommands", help="sub-command help")

    run_test_cmd = sub.add_parser('run_test', help='Run Test')
    run_test_cmd.add_argument("--endpoint", required=True, type=str, help="User and Host and port, ie user@host:port")
    run_test_cmd.add_argument("--script", required=True, type=str, help="script to run")
    run_test_cmd.add_argument("--files", type=str, nargs="*", help="Files to copy, each string must be a pair of src:dest joined by a colon")
    run_test_cmd.set_defaults(func=_run_test_args)

    remote_ps_cmd = sub.add_parser('remote_ps', help='Stop Local Container')
    remote_ps_cmd.add_argument("--cluster", type=str, default=ECS_DEFAULT_CLUSTER, help="ECS Cluster to target")
    remote_ps_cmd.set_defaults(func=_remote_ps_container_args)

    remote_create_cmd = sub.add_parser('remote_create', help='Create Remote Container')
    remote_create_cmd.add_argument("--cluster", type=str, default=ECS_DEFAULT_CLUSTER, help="ECS Cluster to target")
    remote_create_cmd.add_argument("--service", type=str, default=DEFAULT_SERVICE_NAME, help="ECS Service to create")
    remote_create_cmd.add_argument("--task_definition", type=str, default=ECS_DEFAULT_TASK_DEFINITION, help="ECS Task Definition to use to create service")
    remote_create_cmd.add_argument("--subnets", type=str, nargs="*", default=ECS_DEFAULT_SUBNETS, help="EC2 subnets to use")
    remote_create_cmd.add_argument("--security_group", type=str, default=ECS_DEFAULT_SECURITY_GROUP, help="EC2 security group use")
    remote_create_cmd.set_defaults(func=_remote_create_container_args)

    remote_stop_cmd = sub.add_parser('remote_stop', help='Stop Remote Container')
    remote_stop_cmd.add_argument("--cluster", type=str, default=ECS_DEFAULT_CLUSTER, help="ECS Cluster to target")
    remote_stop_cmd.add_argument("--service", type=str, default=DEFAULT_SERVICE_NAME, help="ECS Service to stop")
    remote_stop_cmd.set_defaults(func=_remote_stop_container_args)

    remote_gc_services_cmd = sub.add_parser('remote_gc_services', help='GC Remote Container')
    remote_gc_services_cmd.add_argument("--cluster", type=str, default=ECS_DEFAULT_CLUSTER, help="ECS Cluster to target")
    remote_gc_services_cmd.set_defaults(func=_remote_gc_services_container_args)

    get_caller_identity_cmd = sub.add_parser('get_caller_identity', help='Get the AWS IAM caller identity')
    get_caller_identity_cmd.set_defaults(func=_get_caller_identity)

    remote_get_endpoint_cmd = sub.add_parser('remote_get_endpoint', help='Get SSH remote endpoint')
    remote_get_endpoint_cmd.add_argument("--cluster", type=str, default=ECS_DEFAULT_CLUSTER, help="ECS Cluster to target")
    remote_get_endpoint_cmd.add_argument("--service", type=str, default=DEFAULT_SERVICE_NAME, help="ECS Service to stop")
    remote_get_endpoint_cmd.set_defaults(func=_remote_get_endpoint_args)

    run_e2e_test_cmd = sub.add_parser('run_e2e_test', help='Run Test')
    run_e2e_test_cmd.add_argument("--script", required=True, type=str, help="script to run")
    run_e2e_test_cmd.add_argument("--files", type=str, nargs="*", help="Files to copy, each string must be a pair of src:dest joined by a colon")
    run_e2e_test_cmd.add_argument("--cluster", type=str, default=ECS_DEFAULT_CLUSTER, help="ECS Cluster to target")
    run_e2e_test_cmd.add_argument("--task_definition", type=str, default=ECS_DEFAULT_TASK_DEFINITION, help="ECS Task Definition to use to create service")
    run_e2e_test_cmd.add_argument("--subnets", type=str, nargs="*", default=ECS_DEFAULT_SUBNETS, help="EC2 subnets to use")
    run_e2e_test_cmd.add_argument("--security_group", type=str, default=ECS_DEFAULT_SECURITY_GROUP, help="EC2 security group use")
    run_e2e_test_cmd.set_defaults(func=_run_e2e_test_args)

    args = parser.parse_args()

    print("AWS_SHARED_CREDENTIALS_FILE: %s" % (os.getenv("AWS_SHARED_CREDENTIALS_FILE")))

    if args.debug:
        logging.basicConfig(level=logging.DEBUG)
    elif args.verbose:
        logging.basicConfig(level=logging.INFO)


    args.func(args)


if __name__ == "__main__":
    main()
