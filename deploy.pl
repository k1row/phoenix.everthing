#!/usr/bin/perl

use strict;
use Data::Dumper;

my $pem = "/Users/nagashima/Home/amoaduskey.pem";
my $ignore = "--exclude=.DS_Store --exclude=Config/core.php --exclude=Config/database.php --exclude=Config/database.php.default --exclude=Config/bootstrap.php --exclude=tmp --exclude=webroot/js --exclude=XTAGS";
my @target = ('54.241.33.150', 'ec2-184-169-238-151.us-west-1.compute.amazonaws.com');

my $base_dir = "/usr/local/nginx/cake/app";
my $target_dir = "/usr/local/nginx/cake";

my $option = $ARGV[0];
if ($option eq "push")
{
  my $cmd = "rsync -avlCn $ignore -e " . "'ssh -i ". $pem. "' $base_dir" . " nginx@" . $target[0]. ":$target_dir";
  print $cmd."\n";
  my $result = system $cmd;
  my $msg = $result . "\n Do you really want to upload these files ? [yes/no]";
  print STDOUT $msg;

  my $answer = <STDIN>;
  chomp $answer;

  exit if ($answer !~ m/yes/i);


  print STDOUT "Here we go !!\n\n";

  foreach (@target)
  {
    print STDOUT "For [$_]";
    my $cmd = "rsync -avlC $ignore -e " . "'ssh -i ". $pem. "' $base_dir" . " nginx@" . $_. ":$target_dir";
    print $cmd."\n";
    my $result = system $cmd;

    print STDOUT $result;
    print STDOUT "\n\n";
  }
}
elsif ($option eq "pull")
{
  my $cmd = "rsync -avlCn $ignore -e " . "'ssh -i ". $pem. "' nginx@". $target[0]. ":$base_dir" . " $target_dir";
  print $cmd."\n";
  my $result = system $cmd;
  my $msg = $result . "\n Do you really want to download these files ? [yes/no]";
  print STDOUT $msg;

  my $answer = <STDIN>;
  chomp $answer;

  exit if ($answer !~ m/yes/i);

  $cmd = "rsync -avlC $ignore -e " . "'ssh -i ". $pem. "' nginx@". $target[0]. ":$base_dir" . " $target_dir";
  my $result = system $cmd;

  print STDOUT $result;
  print STDOUT "\n\n";
}
elsif ($option eq "pushadmin")
{
  $base_dir = "/usr/local/nginx/cakeAdmin/app";
  $target_dir = "/usr/local/nginx/cakeAdmin";

  my $cmd = "rsync -avlCn $ignore -e " . "'ssh -i ". $pem. "' $base_dir" . " nginx@" . $target[0]. ":$target_dir";
  print $cmd."\n";
  my $result = system $cmd;
  my $msg = $result . "\n Do you really want to upload these files ? [yes/no]";
  print STDOUT $msg;

  my $answer = <STDIN>;
  chomp $answer;

  exit if ($answer !~ m/yes/i);

  print STDOUT "Here we go !!\n\n";

  $cmd = "rsync -avlC $ignore -e " . "'ssh -i ". $pem. "' $base_dir" . " nginx@" . $target[0]. ":$target_dir";
  my $result = system $cmd;

  print STDOUT $result;
  print STDOUT "\n\n";
}
elsif ($option eq "pushcbord")
{
  $base_dir = "/usr/local/nginx/cakeCDashBoard/app";
  $target_dir = "/usr/local/nginx/cakeCDashBoard";

  my $cmd = "rsync -avlCn $ignore -e " . "'ssh -i ". $pem. "' $base_dir" . " nginx@" . $target[0]. ":$target_dir";
  print $cmd."\n";
  my $result = system $cmd;
  my $msg = $result . "\n Do you really want to upload these files ? [yes/no]";
  print STDOUT $msg;

  my $answer = <STDIN>;
  chomp $answer;

  exit if ($answer !~ m/yes/i);

  print STDOUT "Here we go !!\n\n";

  $cmd = "rsync -avlC $ignore -e " . "'ssh -i ". $pem. "' $base_dir" . " nginx@" . $target[0]. ":$target_dir";
  my $result = system $cmd;

  print STDOUT $result;
  print STDOUT "\n\n";
}
else
{
  print STDOUT "usage <push> or <pull> \n";
}
exit;
