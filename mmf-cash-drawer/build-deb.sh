#!/bin/sh
echo "Copying files..."
mkdir tmp/
cp -R deb/* tmp/
cp -R firefox-extension/* tmp/usr/lib/firefox-addons/extensions/
cp mmf-drawer/dist/Release/GNU-Linux-x86/mmf_drawer tmp/usr/lib/firefox-addons/extensions/pinescashdrawer@hunter.perrin/drawer
echo "Building package..."
dpkg -b tmp/ firefox-pines-cash-drawer-mmf.deb
echo "Cleaning up..."
rm -r tmp/
echo "Done."
