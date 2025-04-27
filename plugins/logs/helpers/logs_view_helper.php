<?php

function getLogLevelClass($level) {
    switch (strtolower($level)) {
        case 'emergency': return 'text-danger fw-bold';
        case 'alert':     return 'text-danger';
        case 'critical':  return 'text-warning fw-bold';
        case 'error':     return 'text-warning';
        case 'warning':   return 'text-warning';
        case 'notice':    return 'text-primary';
        case 'info':      return 'text-info';
        case 'debug':     return 'text-muted';
        default:          return 'text-body'; // fallback normal text
    }
}
