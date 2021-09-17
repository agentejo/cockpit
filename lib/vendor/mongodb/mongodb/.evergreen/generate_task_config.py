import itertools


TASK_TEMPLATE = '''
    - name: "test-{version}-{topology}"
      tags: ["{version}", "{topology}"]
      commands:
        - func: "bootstrap mongo-orchestration"
          vars:
            VERSION: "{version}"
            TOPOLOGY: "{mo_topology}"
        - func: "run tests"'''

MONGODB_VERSIONS = ['2.4', '2.6', '3.0', '3.2', '3.4', 'latest']
TOPOLOGY_OPTIONS = ['standalone', 'replica_set', 'sharded_cluster']


def create_task(version, topology):
    mo_topology= topology
    # mongo-orchestration uses 'server' as the name for 'standalone'
    if mo_topology == 'standalone':
        mo_topology = 'server'
    return TASK_TEMPLATE.format(**locals())


tasks = []
for version, topology in itertools.product(MONGODB_VERSIONS,
                                           TOPOLOGY_OPTIONS):
    tasks.append(create_task(version, topology))

print('\n'.join(tasks))
