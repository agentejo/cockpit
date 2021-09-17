/**
 * Validate that MONGODB-AWS auth works from ECS temporary credentials.
 */
load("lib/aws_e2e_lib.js");

(function() {
   'use strict';

   assert.eq(typeof mongo_binaries != 'undefined', true, "mongo_binaries must be set");
   assert.eq(typeof project_dir != 'undefined', true, "project_dir must be set");

   const config = readSetupJson();

   const base_command = getPython3Binary() + " -u  lib/container_tester.py";
   const run_prune_command = base_command + ' -v remote_gc_services ' +
       ' --cluster ' + config['iam_auth_ecs_cluster'];

   const run_test_command = base_command + ' -d -v run_e2e_test' +
       ' --cluster ' + config['iam_auth_ecs_cluster'] + ' --task_definition ' +
       config['iam_auth_ecs_task_definition'] + ' --subnets ' +
       config['iam_auth_ecs_subnet_a'] + ' --subnets ' +
       config['iam_auth_ecs_subnet_b'] + ' --security_group ' +
       config['iam_auth_ecs_security_group'] +
       ` --files ${mongo_binaries}/mongod:/root/mongod ${mongo_binaries}/mongo:/root/mongo ` +
       " lib/ecs_hosted_test.js:/root/ecs_hosted_test.js " +
       `${project_dir}:/root` +
       " --script lib/ecs_hosted_test.sh";

   // Pass in the AWS credentials as environment variables
   // AWS_SHARED_CREDENTIALS_FILE does not work in evergreen for an unknown
   // reason
   const env = {
      AWS_ACCESS_KEY_ID: config['iam_auth_ecs_account'],
      AWS_SECRET_ACCESS_KEY: config['iam_auth_ecs_secret_access_key'],
   };

   // Prune other containers
   let ret = runWithEnv(['/bin/sh', '-c', run_prune_command], env);
   assert.eq(ret, 0, 'Prune Container failed');

   // Run the test in a container
   ret = runWithEnv(['/bin/sh', '-c', run_test_command], env);
   assert.eq(ret, 0, 'Container Test failed');
}());
