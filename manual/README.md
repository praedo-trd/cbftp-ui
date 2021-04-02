# Introduction

[https://gitlab.com/trdgit/trd](https://gitlab.com/trdgit/trd)

TRD is a daemon that allows you to determine which sites a particular release
should be traded to, and the corresponding bookmark. It is primary designed to
work with cbftp, but can be modified to work with other clients.

An example flow is:
- You connect IRC client to TRD over TCP channel, and TRD receieves messages 
- TRD detects that a particular message looks like mkdir on a site channel 
- TRD determines the bookmark that the release belongs to 
- TRD determines which sites are allowed to receieve it, and returns the list of sites

In essence, releasename comes in, site list comes out.
