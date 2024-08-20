#!/bin/bash

#!/bin/bash
iptables -t mangle -N SINGBOX
iptables -t mangle -A SINGBOX -p tcp -j MARK --set-mark 1
iptables -t mangle -A SINGBOX -p udp -j MARK --set-mark 1
iptables -t mangle -A PREROUTING -j SINGBOX
iptables -t mangle -A OUTPUT -j SINGBOX

/usr/bin/sing-box run -c /etc/neko/config/config.json