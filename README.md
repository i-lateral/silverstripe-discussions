Silverstripe Discussions
========================

Simple forum module for silverstripe that uses a more blog
like list of discussions with categories instead of the 
traditional "folder" structure.

The idea is to create a module similar to Yahoo / Google
groups or Vanilla Forum.

## Requirements

 * SilverStripe 3.2
 * [silverstripe-australia/silverstripe-gridfieldextensions](https://github.com/silverstripe-australia/silverstripe-gridfieldextensions): SilverStripe Grid Field Extensions Module
 * [silverstripe/silverstripe-comments](https://github.com/silverstripe/silverstripe-comments): Comments

## Maintainer Contact

 * Mo <morven@ilateral.co.uk>

## Installation

The preffered method of installation is Composer (see the
[official docs](https://docs.silverstripe.org/en/3/developer_guides/extending/modules/#installation))

To install via composer run the following:

    composer require i-lateral/silverstripe-discussions

NOTE: The above will install the latest tagged release

## Basic Usage

Once installed, make sure you run a `dev/build` to add the new page type, categories, etc.

This will also automatically add a "Discussions" Page to your site tree, and a "poster" and "moderator" security group.

### Adding Categories

Discussions can be categoriesed by simple category objects. You can add categories by navigating to the discussions page in the CMS and adding categories.

### Starting (and commenting on) Discussions

In order for a user to start a discussion, they must have the "DISCUSSIONS_POSTING" permission (this is also true for commenting on a discussion).

By default users will get this permission by registering for an account and then clicking the link in the verification email that the site sends them.

If you wish to disable this functionality, you will need to change the registration configuration of the users module (add the following to your `config.yml`):

```
Users:
  new_user_groups:
    - discussions-posters
  require_verification: false
  send_verification_email: false
```

**NOTE** It is not advisable to do this, the verification adds an extra layer of spam protection (otherwise bots or spammers could easily sign up with fictitious accounts and post messages).