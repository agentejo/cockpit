/**
 * Validate that the server supports real credentials from AWS and can talk to a real AWS STS
 * service
 */
load("lib/aws_e2e_lib.js");

(function() {
"use strict";

const admin = Mongo().getDB("admin");
const external = admin.getMongo().getDB("$external");
assert(admin.auth("bob", "pwd123"));

const config = readSetupJson();
assert.commandWorked(
    external.runCommand({createUser: config["iam_auth_ecs_account_arn"], roles:[{role: 'read', db: "aws"}]}));

assert(external.auth({
    user: config["iam_auth_ecs_account"],
    pwd: config["iam_auth_ecs_secret_access_key"],
    mechanism: 'MONGODB-AWS'
}));
}());