Unsplash Fetch (unsplash-dl)
==============

A simple script to download pictures from the tumblelog [Unsplash](http://unsplash.com/).
Runs in the background with launchd on OS X, could be adapted on other systems.
See also [New Old Stock Fetch](https://github.com/mcdado/newoldstock-dl)

Setup
-----

0. Download this repo in ZIP file and extract it wherever you want on your system.
1. Open Terminal, `cd` in the folder containing the repo files.
2. Execute: `mkdir -p ~/Pictures/Unsplash`
3. Execute: `ln -sfv "$(pwd)/unsplash-dl.php" /usr/local/bin`
4. Execute: `ln -sfv "$(pwd)/com.mcdado.unsplash.plist" ~/Library/LaunchAgents/`
5. Execute: `launchctl load ~/Library/LaunchAgents/com.mcdado.unsplash.plist`
6. Setup your preferred OS X Screensaver to look into `~/Pictures/Unsplash`

Notes
-----

* I assume you are using homebrew's php. If you're not, edit the file `unsplash-dl.php` at the first line with `#!/usr/bin/php`;
* The script requires [pecl\_http v2](http://pecl.php.net/package/pecl_http). You can install pecl_http with homebrew (php5*-http) or via PECL.
* The code is quite smelly, but it's that way because it is an experiment and I wanted to make it work quickly... any suggestions and improvements are welcome.
