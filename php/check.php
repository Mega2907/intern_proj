<?php
echo extension_loaded("mongodb") ? "MongoDB Loaded!" : "MongoDB NOT Found!";
echo "<br>";
echo extension_loaded("redis") ? "Redis Loaded!" : "Redis NOT Found!";
?>