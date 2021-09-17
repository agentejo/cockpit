/**
 * Verify the AWS IAM ECS hosted auth works
 */

(function() {
"use strict";

// This varies based on hosting ECS task as the account id and role name can vary
const AWS_ACCOUNT_ARN = "arn:aws:sts::557821124784:assumed-role/ecsTaskExecutionRole/*";

const conn = MongoRunner.runMongod({
    setParameter: {
        "authenticationMechanisms": "MONGODB-AWS,SCRAM-SHA-256",
    },
    auth: "",
});

const external = conn.getDB("$external");
const admin = conn.getDB("admin");

assert.commandWorked(admin.runCommand({createUser: "admin", pwd: "pwd", roles: ['root']}));
assert(admin.auth("admin", "pwd"));

assert.commandWorked(external.runCommand({createUser: AWS_ACCOUNT_ARN, roles:[{role: 'read', db: "aws"}]}));

const uri = "mongodb://127.0.0.1:20000/aws?authMechanism=MONGODB-AWS";
const program = "/root/src/.evergreen/run-mongodb-aws-ecs-test.sh";

// Try the command line
const smoke = runMongoProgram(program, uri);
assert.eq(smoke, 0, "Could not auth with smoke user");

// Try the auth function
assert(external.auth({mechanism: 'MONGODB-AWS'}));

MongoRunner.stopMongod(conn);
}());
