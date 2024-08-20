  <img src="https://raw.githubusercontent.com/Thaolga/neko/main/img/im.png" >
# openwrt一键安装脚本
---

```bash
wget -O /root/nekoclash.sh https://raw.githubusercontent.com/Thaolga/luci-app-nekoclash/main/nekoclash.sh && chmod 0755 /root/nekoclash.sh && /root/nekoclash.sh

```

# openwrt编译
---
## 克隆源码 :
---

```bash
git clone https://github.com/luci-app-nekoclash  package/luci-app-nekoclash

```

## 编译 :
---

```bash
make package/luci-app-nekoclash/{clean,compile} V=s
```
