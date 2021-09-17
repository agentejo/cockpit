# Generating Test Certificates

The test certificates here were generating using a fork of the server
team's 
[`mkcert.py`](https://github.com/mongodb/mongo/blob/master/jstests/ssl/x509/mkcert.py)
tool.

In order to generate a fresh set of certificates, clone this branch of
a fork of the 
[`mongo` repository](https://github.com/vincentkam/mongo/tree/mkcert-ecdsa) and
run the following command from the root of the `mongo` repository:

`python3 jstests/ssl/x509/mkcert.py --config ../drivers-evergreen-tools/.evergreen/ocsp/certs.yml`

Passing a certificate ID as the final parameter will limit certificate
generation to that certificate and all its leaves. Note: if
regenerating ECDSA leaf certificates, ``ecsda/ca.pem`` will need to be
temporarily renamed back to ``ecdsa-ca-ocsp.pem``.

The ECDSA certificates will be output into the folder specified by the
`global.output_path` option in the `certs.yml` file, which defaults to
`ecsda` directory contained in this directory. The RSA certificate
definitions override this value on a per certificate basis and are
output into the `rsa` directory. The default configuration also
assumes that the `mongo` repository and the `driver-evergreen-tools`
repository have the same parent directory.

After generating the RSA root certificate, one must manually split the
`rsa/ca.pem` file, which contains both the private key and the public
certificate, into two files. `rsa/ca.crt` should contain the public
certificate, and `ras/ca.key` should contain the private certificate.

When generating ECDSA certificates, one must normalize the ECDSA
certificate names by running `ecdsa/rename.sh`.
