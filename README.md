# Secrets

CLI PHP vault based on [symfony/framework-bundle](https://github.com/symfony/framework-bundle)

## Install

```
composer require casahugo/secrets
```

### Generate Cryptographic Keys
In order to encrypt and decrypt secrets, application needs cryptographic keys. A pair of keys can be generated by running:
```
php vendor/bin/secrets --dir=config/secrets generate-keys
```
This will generate a pair of asymmetric cryptographic keys. Each environment has its own set of keys. Assuming you're coding locally in the dev environment, this will create:

`config/secrets/dev/dev.encrypt.public.php`

Used to encrypt/add secrets to the vault. Can be safely committed.


`config/secrets/dev/dev.decrypt.private.php`

Used to decrypt/read secrets from the vault. The dev decryption key can be committed (assuming no highly-sensitive secrets are stored in the dev vault) but the prod decryption key should never be committed.


You can generate a pair of cryptographic keys for the prod environment by running:
```
APP_ENV=prod php vendor/bin/secrets --dir=config/secrets generate-keys
```
This will generate `config/secrets/prod/prod.encrypt.public.php` and `config/secrets/prod/prod.decrypt.private.php`.

---
🚨
**The prod.decrypt.private.php file is highly sensitive. Your team of developers and even Continuous Integration services don't need that key. If the decryption key has been exposed (ex-employee leaving for instance), you should consider generating a new one by running: secrets:generate-keys --rotate.**
---

### Create or Update Secrets
Suppose you want to store your database password as a secret. By using the `set` command, you should add this secret to both the dev and prod vaults:
```
# the input is hidden as you type for security reasons

# set your default development value (can be overridden locally)
php vendor/bin/secrets --dir=config/secrets set DATABASE_PASSWORD

# set your production value
APP_ENV=prod php vendor/bin/secrets --dir=config/secrets set DATABASE_PASSWORD
```

### List Existing Secrets
Everybody is allowed to list the secrets names with the command `list`. If you have the decryption key you can also reveal the secrets' values by passing the --reveal option:
```
php vendor/bin/secrets --dir=config/secrets list --reveal

 ------------------- ------------ -------------
  Name                Value        Local Value
 ------------------- ------------ -------------
  DATABASE_PASSWORD   "my secret"
 ------------------- ------------ -------------
```

### Remove Secrets
```
php vendor/bin/secrets --dir=config/secrets secrets:remove DATABASE_PASSWORD
```

### Local secrets: Overriding Secrets Locally
The `dev` environment secrets should contain nice default values for development. But sometimes a developer still needs to override a secret value locally when developing.

Most of the secrets commands - including `set` - have a `--local` option that stores the "secret" in the `.env.{env}.local` file as a standard environment variable. To override the `DATABASE_PASSWORD` secret locally, run:
```
APP_ENV=prod php vendor/bin/secrets --dir=config/secrets set DATABASE_PASSWORD --local
```
If you entered root, you will now see this in your `.env.dev.local` file:

`DATABASE_PASSWORD=root`

This will override the `DATABASE_PASSWORD` secret because environment variables always take precedence over secrets.

Listing the secrets will now also display the local variable:
```
php vendor/bin/secrets --dir=config/secrets list --reveal
 ------------------- ------------- -------------
  Name                Value         Local Value
 ------------------- ------------- -------------
  DATABASE_PASSWORD   "dev value"   "root"
 ------------------- ------------- -------------
```
Commands also provides the `decrypt-to-local` command which decrypts all secrets and stores them in the local vault and the `encrypt-from-local` command to encrypt all local secrets to the vault.

### Rotating Secrets
The `generate-keys` command provides a `--rotate` option to regenerate the cryptographic keys. Application will decrypt existing secrets with the old key, generate new cryptographic keys and re-encrypt secrets with the new key. In order to decrypt previous secrets, the developer must have the decryption key.
