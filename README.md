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