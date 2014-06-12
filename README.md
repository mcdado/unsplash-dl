Unsplash Fetch (unsplash-dl)
==============

A simple script to download pictures from the tumblelog Unsplash.
Runs in the background with launchd.

Setup
-----

1. Open Terminal, `cd` in the folder containing the repo files.
2. Execute: `mkdir -p ~/Pictures/Unsplash`
3. Execute: `ln -sfv "$(pwd)/unsplash-dl.php" /usr/local/bin`
4. Execute: `ln -sfv "$(pwd)/com.mcdado.unsplash.plist" ~/Library/LaunchAgents/`
5. Execute: `launchctl load ~/Library/LaunchAgents/com.mcdado.unsplash.plist`
6. Setup your preferred OS X Screensaver to look into `~/Pictures/Unsplash`

Notes
-----

* I assume you are using homebrew's php. If you're not, edit the file `unsplash-dl.php` at the first line with `#!/usr/bin/php`
* The script requires [pecl\_http v2](http://pecl.php.net/package/pecl_http). You can install pecl_http with homebrew (php5*-http) or with PECL.
* It's very ugly code, but it's also just an experiment and I wanted to make it work quickly... any suggestions and improvements are welcome.
