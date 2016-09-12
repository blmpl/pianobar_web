# pianobar_web
Web client for pianobar cli

Just copy a web folder to your nginx/apache/... www directory,
update ~/.config/pianobar/config to:

```
fifo = /tmp/pianobar_ctl
event_command = {path_to_this_dir}/pl/pianobar_status.pl
```

run:
```
mkfifo /tmp/pianobar_ctl
chmod 666 /tmp/pianobar_ctl
```

and start pianobar on console ;)

