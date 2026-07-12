# Postponed

Postponed is a prototype for a social network that has a different approach to time than other existing networks : nothing you write there can be read right away.

By default, each status update is only displayed one hundred years after it was posted.

You can spend points to make a post arrive sooner : the first point leaves the delay at one hundred years, then each extra point divides it by two. Two points bring it down to fifty years, ten points to about ten weeks, and for twenty-five points the post shows up after roughly three minutes, which is the closest thing to instant messaging that the site allows.

A user gets thirty points when registering, then one more point every day. Posting always costs at least one point.

You can follow the other users, their posts will appear in your feed once their dates have come. Until then, the feed only shows that something was posted and when it will become readable. Your own pending posts stay readable to you, with their arrival date.

It can be tried here : https://www.officialdatabase.org/postponed/

More about the project : https://nurykabe.com/dump/docs/postponed/

## How it works

Plain PHP (7.0 or later) with jQuery on the client side. There is no database : each user is an XML file in `users/`, plus a `users/global.xml` index.

## Deploying it

Copy the files onto any PHP-enabled Apache server, that's it. The `users/` folder and its index are created automatically on the first request.

A few things to check :

- the web server must be allowed to write in `users/` (posting will say so explicitly if it can't) ;
- `users/.htaccess` must be uploaded too, it blocks direct web access to the data files, which contain the password hashes and the posts that are not supposed to be readable yet ;
- https is strongly recommended since passwords travel with the requests (the root `.htaccess` redirects http to https).

About the permissions : the files that PHP creates itself get usable permissions automatically, so a fresh install has nothing to configure. It's only if you upload data files manually (by FTP for instance) that they end up belonging to your FTP account and PHP can lose the right to modify them. In that case set `users/` to 775 and the `.xml` files inside to 664 (or 777 and 666 if the web server user doesn't share your group).

The `users/` folder of the live site is deliberately not part of this repository.
