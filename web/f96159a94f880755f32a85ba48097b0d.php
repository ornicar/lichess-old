<?php

if(false)   apc_clear_cache('user');
if(true) apc_clear_cache('opcode');

die(json_encode(array('success' => true, 'message' => sprintf('Clear APC user:false, opcode:true'))));
