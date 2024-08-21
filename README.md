<h1 align="center">
  <img src="https://raw.githubusercontent.com/Thaolga/neko/main/img/neko.png" alt="neko" width="500">
</h1>

<div align="center">
 <a target="_blank" href="https://github.com/Thaolga/luci-app-nekoclash/releases"><img src="https://img.shields.io/github/downloads/nosignals/neko/total?label=Total%20Download&labelColor=blue&style=for-the-badge"></a>
 <a target="_blank" href="https://dbai.team/discord"><img src="https://img.shields.io/discord/1127928183824597032?style=for-the-badge&logo=discord&label=%20"></a>
</div>


<p align="center">
  XRAY/V2ray, Shadowsocks, ShadowsocksR, etc.</br>
  Mihomo based Proxy
</p>

# 此项目自1.1.33开始添加对Sing-box的支持，只支持firewall4 + nftables 原Mihomo功能不受影响
---


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

  <img src="https://raw.githubusercontent.com/Thaolga/neko/main/img/im.png" >
