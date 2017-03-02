# hash-posts
This is a Facebook application that enables a user to parse all hashtags in a Facebook post and automatically tweets those hashtags in the user's Twitter wall.
This uses the facebook webhooks to keep track of when a user has posted on his wall, negating the use running cron jobs to keep track of it.

To Intall - 
- Clone the Directory
- In the root folder, run composer install
- Open headers.php and enter your APP details (Both for Facebook and Twitter)
