#/bin/bash
if [[ ! -d /tmp/images ]]; then
  mkdir -p /tmp/images
  cp -r images/* /tmp/images/
fi

mysql -u root < create.sql
