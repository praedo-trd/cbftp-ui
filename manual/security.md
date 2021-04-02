# Security

## General guidelines
- Never, ever, ever bind on anything other than a local address. Do not use `0.0.0.0`.
- Always browse web GUI in an incognito tab.
- Clone the git repository using a proxy so you don't connect your shell to the repository and leave a trace.

## Guides

### Remote connection

TRD has no authentication and runs on a socket. If you bind it to 127.0.0.1 (localhost) on your shell, you probably cannot access it directly. To work around this we will use SSH tunnels to create a secure
channel between you and TRD.

This guide assumes are you using a *nix command line. If you are an idiot and use Windows - use putty and find a tutorial.

#### Prerequisites

On the machine you are connecting from run the following:

Linux: `sudo apt-get install autossh`
OSX: `brew install autossh`


#### Connecting

`autossh -M 0 -N remote -o "ServerAliveInterval 60" -o "ServerAliveCountMax 3" -L 127.0.0.1:13501:127.0.0.1:13501 -L 127.0.0.1:10000:127.0.0.1:10000`

Replace `remote` with the hostname or IP of your shell. And replace the relevant ports depending on how you have configured TRD. You should now be able to go to `https://127.0.0.1:13501` in your browser and view the web GUI locally.
