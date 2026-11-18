<?php
// Empty index to prevent directory listing of uploaded files.
http_response_code(403);
exit('Forbidden');
