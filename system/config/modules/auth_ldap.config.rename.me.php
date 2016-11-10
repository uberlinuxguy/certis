<?php
# Configuration example for the LDAP Authentication Class.

$conf->auth_ldap = new StdClass();

# the search base for user objects in the directory
$conf->auth_ldap->base = "dc=something,dc=com";

# the filter to use to find people
$conf->auth_ldap->filter = "(objectClass=inetOrgPerson)";

# the LDAP URL for the directory server
$conf->auth_ldap->url = "ldap://ldap.something.com/";

# should we use starttls?
$conf->auth_ldap->starttls = false;

# the distinguished name to bind as
$conf->auth_ldap->binddn = "cn=root,dc=something,dc=com";

# the bind password to use to bind to the directory.
$conf->auth_ldap->bindpw = "set-this";

# Attribute map.  The following are the strings within
# the directory that map to the following attributes:

# maps to the unix username
$conf->auth_ldap->username_attr = "uid";

# maps to the unix user id
$conf->auth_ldap->uid_attr = "uidNumber";

# map the first name of the user
$conf->auth_ldap->fname_attr = "cn";

# map the last name of the user
$conf->auth_ldap->lname_attr = "sn";

# map the Primary Unix GID of the user.
$conf->auth_ldap->gid_attr = "gidNumber";

# maps the home directory of the user
$conf->auth_ldap->hdir_attr = "homeDirectory";

# maps to the shell for the user.
$conf->auth_ldap->shell_attr = "loginShell";
