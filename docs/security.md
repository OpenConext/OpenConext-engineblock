# Security measures for Engineblock

## HTTP Headers

HTTP Headers are set not by Engineblock itself so the deployer needs to set these in
the webserver that serves the EB requests.

We recommend at least:
* Strict-Transport-Security: max-age=<high enough value>
* X-Content-Type-Options: nosniff
* X-Frame-Options: DENY
* Content-Security-Policy: TODO

## PHP settings

We recommend to set `disable_functions` to:

```
exec,passthru,shell_exec,system,popen,curl_multi_exec,show_source,pcntl_alarm,pcntl_fork,pcntl_waitpid,pcntl_wait,pcntl_wifexited,pcntl_wifstopped,pcntl_wifsignaled,pcntl_wifcontinued,pcntl_wexitstatus,pcntl_wtermsig,pcntl_wstopsig,pcntl_signal,pcntl_signal_dispatch,pcntl_get_last_error,pcntl_strerror,pcntl_sigprocmask,pcntl_sigwaitinfo,pcntl_sigtimedwait,pcntl_exec,pcntl_getpriority,pcntl_setpriority`
```

This is of relevance specifically to limit the scope of what Attribute Manipulations
(which are PHP code) are able to accomplish.
