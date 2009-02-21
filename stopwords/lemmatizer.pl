#!/usr/bin/perl
#
# Quick and dirty hack by Martin Hassel for the Webmin course, 2009.
#
# This program is free software; you can redistribute it and/or modify it under
# the terms of either the Artistic License (which comes with Perl 5) or the
# GNU General Public License as published by the Free Software Foundation;
# either version 3 of the License, or (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
# GNU General Public License for more details.
#
# A full copy of the GNU General Public License can be retrieved from
# http://www.gnu.org/copyleft/gpl.html

# Grab rule set path from command line argument
$rules = @ARGV[0];

# Process each file in turn
&recursive(".");
print "\n";

# Recursively traverse a directory tree
sub recursive {
  local($dir) = @_;
  &searchdir($dir);
  foreach $dir (<*>) {
    if(-d $dir) {
      chdir $dir;
      &recursive($dir);
      chdir "..";
      chop($reldir);
      while($reldir =~ /[^\/]$/) {
        $reldir =~ s/[^\/]$//;
      }
    }
  }
}

# Process current directory
sub searchdir {
  local($dir)=@_;
  $reldir .= "$dir/";
  while($reldir =~ /^[.\/]/) { $reldir =~ s/^[.\/]//; }
  opendir (DIR,".") || die ("Can't open "."\n");
  @files = readdir DIR;
  close DIR;
  # Process each file
  foreach $file (sort @files) {
    unless(-d $file or $file =~ /.pl$/i) {
      # Process only text files
      if($file =~ /.txt$/i) {
        # Lemmatise current file using the supplied rules and capture the output
        @lemmas = `cstlemma -e1 -L -f ../$rules -t- -c\"\$B\" -B\"\$w\\n\" < $file`;
        # Remove unnecessary file
        unlink("wordlist.txt") or die "Can't delete file 'wordlist.txt': $!\n";
        # Remove unnecessary output
        shift @lemmas;
        # Delete old file ...
        unlink("$file") or die "Can't delete file '$file': $!\n";
        # ... and replace it with the lemmatized version
        open(LEMMAS,">$file") or die "Cannot open file '$file' for writing: $!";
        print LEMMAS @lemmas;
        close(LEMMAS);
        print ".";
      }
    }
  }
}
