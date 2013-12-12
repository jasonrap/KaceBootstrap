KaceBootstrap
=============

===========================================================
The original project at GitHub: https://github.com/jasonrap/KaceBootstrap
This was only forked due to having no ability to directly communicate with the original author, and I wanted to contribute my changes back to the community.
===========================================================


Twitter Bootstrap for Kace Service Desk

Challenge: our IT staff wanted more from the service desk ticket view.  We wanted to be able to see multiple queues quickly, have priority take center stage, and eliminate columns not necessary.   The built in layout customizer just was not getting there.  In addition, we also wanted to see dashboard level stats on our service desk; i.e. tickets currently open, open by department, and closed per queue by month.   

The solution: Twitter Bootstrap for Kace Service Desk with HighCharts javascript
We used the built in Dell Kace MySQL query account to run quires directly from the KBox.  The quires were created via the service desk using the custom report module or via FlySpeed SQL application.  Once we had the quires we simply embedded them in PHP and wrapped the results around the CSS framework Bootstrap. Chart presentation is being handled by the Javascript framework HighCharts.  Together, we have a solution that allows us to focus on the columns we want to see, view multiple queues, and get a snapshot of how the service desk is doing via a high level dashboard.  

The code: I am certainly not an expert programmer, so feel free to make any changes you deem appropriate.  You will certainly have to customize which queue(s) you are querying.  Aside from that, it should work.  

Let me know what you think.  

Here is the video on how to implement this: https://vimeo.com/80390378

Posted on ITNinja here http://www.itninja.com/blog/view/twitter-bootstrap-for-kace-service-desk and http://www.itninja.com/blog/view/twitter-bootstrap-for-kace-service-desk-the-video


===========================================================
Files that will need to be edited to fit your organization:
===========================================================
includes/config.php - REQUIRED. Your KBox's DNS name and your report user's password (from Settings -> General Settings). Also, your main (default) ticket queue ID and name.
includes/nav.php - REQUIRED. Update the userList array with your users.
index.php - Pick the reports in the switch() at line 70 that you want to uncomment if you have the need.
buildTickets.php - if you have a queue for computer builds, otherwise, don't worry.
ownerUnassigned.php - You may want to rename the Custom Field 0 column (or remove it altogether if you don't use it).
reportByQueueClosed.php - REQUIRED. Queue IDs and plot titles. You can disable a couple plots too.
reportForOwner12Months.php - Number of months and chart title (or don't worry if you like the defaults).
webTickets.php - If you have a web-generated tickets queue, edit the queue ID in here.
