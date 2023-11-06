#!/bin/bash

VENDOR_CSS=dist/admin/vendor.css
VENDOR_JS=dist/admin/vendor.js

echo '' > $VENDOR_CSS
echo '' > $VENDOR_JS

cat node_modules/awesome-notifications/dist/style.css >> $VENDOR_CSS
cat node_modules/awesome-notifications/dist/modern.var.js >> $VENDOR_JS
