00	0	*	*	*	digestdaily.php	#Daily: Sending e-mail notifications
00	0	*	*	1	digestweekly.php	#Weekly: Sending e-mail notifications
00	4	*	*	*	dboptimize.php	#Daily: Optimizse database tables
00	1	*	*	0	deletegeshi.php	#Weekly: Delete old cached source codes
00	2	*	*	0	deletethumbnails.php	#Weekly: Delete old cached thumbnails
00	3	*	*	0	deletesearch.php	#Weekly: Delete old search results
30	*	*	*	*	deletetemp.php	#Hourly: Delete temporary directory
0	*/6	*	*	*	recountpostcounts.php	#Recount User Post Counter