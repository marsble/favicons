<p align="center">
  <a href="https://statically.io/favicons/">
    <img src="https://cdn.statically.io/img/statically.io/images/branding/statically-mark.svg" alt="Statically" height="80"/>
  </a>
</p>

<h1 align="center">Icon</h1>

<p align="center">Get a website icon by crawling HTML.</p>

<p align="center">
  <a href="https://statically.io">statically.io</a> |
  <a href="https://twitter.com/staticallyio">Twitter</a> |
  <a href="https://statically.discourse.group">Community</a> |
  <a href="https://www.patreon.com/fransallen">Become A Backer</a>
  <br /><br />
	<a href="https://hub.docker.com/r/statically/icon">
    <img src="https://img.shields.io/docker/cloud/build/statically/icon" alt="Docker Cloud Build Status" />
  </a>
  <a href="https://hub.docker.com/r/statically/icon">
    <img src="https://img.shields.io/docker/pulls/statically/icon?color=success" alt="Docker Pulls" />
  </a>
  <a href="https://hub.docker.com/r/statically/icon">
    <img src="https://img.shields.io/docker/image-size/statically/icon/latest?color=success" alt="Docker Image Size" />
  </a>
</p>

## :sparkles: Overview

**Icon** is a simple application to get an icon from a website by crawling the source code. It's designed to be simple and easy to install anywhere.

## :bulb: Features

- Get an icon by crawling HTML.
- Get an icon from `/favicon.ico` path.
- Serve [default.ico](default.ico) if none of the above is set.

## :zap: Installation

We'll be using Docker as an easy way to install. However since this is a PHP application, you can run it with your current setup.

### Run the app

```bash
docker run -d \
  --name icon \
  -p 9000:9000 \
  statically/icon:latest
```

This will start the PHP FPM server so you can add it to your current web server.