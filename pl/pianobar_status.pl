#!/usr/bin/perl -w
use strict;


my $status = '/tmp/pianobar_status';

open(my $fh, ">", $status) or die "Couldn't open $status for writing: $!";
chmod(0644, $fh);

while (<STDIN>) {
      chomp;
      print $fh "$_\n";
}

close($fh);

__END__

Change event_command in pianobar config to 
# event_command = {PATH_TO_THIS_FILE}/status.pl
