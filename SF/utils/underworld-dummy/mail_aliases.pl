#!/usr/bin/perl
#
# $Id$
#
use DBI;

require("../include.pl");  # Include all the predefined functions

$admin_list = ("precision,fusion94,dtype,bigdisk");

&db_connect;

@alias_array = open_array_file("aliases.zone");

push @alias_array, "\n\n### Begin Mailing List Aliases ###\n\n";

$query = "SELECT list_name from mail_group_list";
$c = $dbh->prepare($query);
$c->execute();
while(my ($list_name) = $c->fetchrow()) {
		$list_name =~ tr/A-Z/a-z/;
		$list_name =~ s/ //g;

		push @alias_array, sprintf("%-50s%-10s","$list_name\@$sys_lists_host:", "\"|/usr/local/mailman/mail/wrapper post $list_name\"\n");
		push @alias_array, sprintf("%-50s%-10s","$list_name-admin\@$sys_lists_host:", "\"|/usr/local/mailman/mail/wrapper mailowner $list_name\"\n");
		push @alias_array, sprintf("%-50s%-10s","$list_name-request\@$sys_lists_host:", "\"|/usr/local/mailman/mail/wrapper mailcmd $list_name\"\n");
}




push @alias_array, "\n\n### Begin User Aliases ###\n\n";

$query = "SELECT user_name,email FROM user WHERE status = \"A\"";

$c = $dbh->prepare($query);
$c->execute();
while(($username, $email) = $c->fetchrow()) {
	if ($email) {
		if (!($admin_list =~ /.*$username*./)) {
			push @alias_array, sprintf("%-50s%-10s","$username\@$sys_users_host:", "$email\n");
		}
	}
}

# Retrieve the dummy's home directory
($name,$passwd,$uid,$gid,$quota,$comment,$gcos,$dir,$shell,$expire) = getpwnam("dummy");

write_array_file("$dir/dumps/aliases", @alias_array);
