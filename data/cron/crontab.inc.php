00	0	*	*	*	DigestDaily.php	#Daily: Sending e-mail notifications
00	4	*	*	*	DbOptimize.php	#Daily: Optimizse database tables
00	2	*	*	0	DeleteThumbnails.php	#Weekly: Delete old cached thumbnails
00	3	*	*	0	DeleteSearch.php	#Weekly: Delete old search results
30	*	*	*	*	DeleteTemp.php	#Hourly: Delete temporary directory
0	*/6	*	*	*	RecountPostCounts.php	#Recount User Post Counter
0	5	*	*	*	ExportBoardStats.php	#Daily: Export forum statistics to an ini-file (optional)