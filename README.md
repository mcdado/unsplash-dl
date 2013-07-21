Unsplash Fetch
==============

A simple script to fetch pictures from the tumblelog Unsplash.
Runs in the background with launchd.

Setup
-----

1. mkdir -p ~/Pictures/Unsplash ~/Library/Scripts ~/Library/LaunchAgents
2. cp unsplash-dl.php ~/Library/Scripts/
3. cp com.unsplash.mcdado.plist ~/Library/LaunchAgents/
4. launchctl load ~/Library/LaunchAgents/com.unsplash.mcdado.plist
5. Setup your preferred OS X Screensaver to look into ~/Pictures/Unsplash

Notes
-----

* It's very ugly code, but it's also just an experiment and I wanted to make it work quickly... any suggestions and improvements are welcome.
* Requires [pecl_http](http://pecl.php.net/package/pecl_http). Because I'm lazy and plain curl is ugly and verbose. You can also install pecl_http with homebrew (php5x-http).
