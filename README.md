# VIA Studio Salesforce REPL

### REPL?
REPL is an acronym for a read-eval-print loop. 

From [Wikipedia](https://en.wikipedia.org/wiki/Read%E2%80%93eval%E2%80%93print_loop)

> A simple, interactive computer programming environment that takes single user inputs, evaluates them, and returns the result to the user.

## What does it do?
This is a simple program that executes commands against Salesforce and returns the result as JSON.

## Features

* Configuration profiles to easily connect to different Salesforce accounts
* Query REST API usage information
* Retrieve Salesforce sobject lists, descriptions, and specific object details
* Execute SOQL queries
* Persistent command history (use :arrow_up: :arrow_down: to cycle through the history)
* Pipe command output to other programs (like the amazing [./jq](https://stedolan.github.io/jq/) utility for processing JSON)

## Usage

### Requirements

* [Composer](https://getcomposer.org/)
* PHP 7.0 >
* Access to a Salesforce account
  * Salesforce account username
  * Salesforce account password
  * Salesforce account security token
  * Salesforce OAuth consumer key and secret. This requires a [Salesforce connected app](#creating-a-salesforce-connected-app)

### Installation

1. Clone this repository
2. Inside the project directory, run `composer install`
3. Create the file `config.ini` using `sample.config.ini` as a guide

### Basic usage

1. Run `php artisan repl <profile-name>`
2. Type `?` or `help` in the REPL to see a list of available commands
3. Type `exit` or press `CTRL-D` to exit

### Basic Examples

Command | Description
--------|------------
`o all` | See a list of all available sobjects
`o describe <sobject>` | Describe a single sobject
`o <sobject> <salesforce id>` | Show the details of a specific Salesforce object.
`q <soql>` | Execute an SOQL query
`u` | Display REST API usage information

### Piping
Running a command like `o all` will return *all* of the data about all sobjects. With piping and the great [./jq](https://stedolan.github.io/jq/) utility, you can easily filter those results down.

For example, `o all | jq '.sobjects | .[] | {label: .label}'` will display only the label of those sobject.


## Developer Notes

### Adding new commands

New commands can be added by creating a command class that extends `AbstractCommand` in the **app/Commands** directory.

Commands must have  `$helpText` and `$titleText` properties containing any help text to display and the name of the command. These will be displayed when a user executes the help command.

Commands must have an `aliases` method which returns an array of aliases that can be used to run the command

Commands must have a `run($fields, $parent)` method which will be called when the command is executed.

**`run` method parameters**
`$fields`       An array of everything typed _after_ the command name. Pipes (`|`) are not included in `$fields` as they are executed automatically after the command runs.
`$parent`       An instance of the repl `Console\Command` class. This is useful if your command needs to prompt for input or [display output](https://laravel.com/docs/5.6/artisan#writing-output)

## Misc

### Creating a Salesforce Connected App

This script requires a custom Salesforce Connected App in order to access the Salesforce [REST API](https://developer.salesforce.com/docs/atlas.en-us.api_rest.meta/api_rest/).

1. Click on your profile name in Salesforce and click **Setup**
2. Under **App Setup**, click **Create** and **Apps**, then click the **New** button under **Connected Apps**
3. Give the app a unique name and label.
4. Make sure that **Permitted Users** is set to *All users may self-authorize*
5. Make sure that **IP Relaxation** is set to *Relax IP restrictions*
6. Make sure that **Refresh Token Policy** is set to *Refresh token is valid until revoked*
7. Make sure that **Selected OAuth Scopes** is set to *Full access*
8. You can put anything for **Callback URL** since the script won't actually be redirecting to it.

To edit existing connected apps
1. Click on your profile name in Salesforce and click **Setup**
2. In the **Quick Find** box, type *connected apps*
3. In the results list on the left, click on **Connected Apps**
4. Click **Edit** next to the name of the connect app you wish to edit
