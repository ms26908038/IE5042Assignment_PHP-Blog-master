# Block access to any file/folder starting with a dot (like .git)
RewriteRule "(^|/)." - [F]

# Block direct access to db.php so people can't try to run it directly
<Files "db.php">
Order Allow,Deny
Deny from All
</Files>