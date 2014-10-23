// English Language File
// Translation provided by Timothy Bell

// Buttons
var lblSubmit 				 = "Submit"; // Button value for non-designMode() & non fullsceen RTE
var lblModeRichText 	 = "Switch to RichText Mode"; // Label of the Show Design view link
var lblModeHTML 			 = "Switch to HTML Mode"; // Label of the Show Code view link
var lblPreview				 = "Preview";
var lblSave 				 	 = "Save";
var lblPrint					 = "Print";
var lblSelectAll			 = "Select/Deselect All";
var lblSpellCheck			 = "Spell Check";
var lblCut						 = "Cut";
var lblCopy						 = "Copy";
var lblPaste					 = "Paste";
var lblPasteText       = "Paste as Plain Text";
var lblPasteWord       = "Paste From Word";
var lblUndo						 = "Undo";
var lblRedo						 = "Redo";
var lblHR							 = "Horizontal Rule";
var lblInsertChar			 = "Insert Special Character";
var lblBold						 = "Bold";
var lblItalic					 = "Italic";
var lblUnderline			 = "Underline";
var lblStrikeThrough   = "Strike Through";
var lblSuperscript		 = "Superscript";
var lblSubscript			 = "Subscript";
var lblAlgnLeft				 = "Align Left";
var lblAlgnCenter			 = "Center";
var lblAlgnRight			 = "Align Right";
var lblJustifyFull		 = "Justify Full";
var lblOL							 = "Ordered List";
var lblUL							 = "Unordered List";
var lblOutdent				 = "Outdent";
var lblIndent					 = "Indent";
var lblTextColor			 = "Text Color";
var lblBgColor				 = "Background Color";
var lblSearch					 = "Search And Replace";
var lblInsertLink			 = "Insert Link";
var lblAddImage				 = "Add Image";
var lblInsertTable		 = "Insert Table";
var lblWordCount       = "Word Count";
var lblUnformat        = "Unformat";

// Dropdowns
// Format Dropdown
var lblFormat 				 =  "<option value=\"\" selected>Format</option>";
lblFormat 						 += "<option value=\"<h1>\">Heading 1</option>";
lblFormat 						 += "<option value=\"<h2>\">Heading 2</option>";
lblFormat 						 += "<option value=\"<h3>\">Heading 3</option>";
lblFormat 						 += "<option value=\"<h4>\">Heading 4</option>";
lblFormat 						 += "<option value=\"<h5>\">Heading 5</option>";
lblFormat 						 += "<option value=\"<h6>\">Heading 6</option>";
lblFormat 						 += "<option value=\"<p>\">Paragraph</option>";
lblFormat 						 += "<option value=\"<address>\">Address</option>";
lblFormat 						 += "<option value=\"<pre>\">Preformatted</option>";
// Font Dropdown
var lblFont 					 =  "<option value=\"\" selected>Font</option>";
lblFont 							 += "<option style=\"font-family: Arial\" value=\"Arial, Helvetica, sans-serif\">Arial</option>";
lblFont 							 += "<option style=\"font-family: Comic Sans MS\" value=\"Comic Sans MS\">Comic Sans MS</option>";
lblFont 							 += "<option style=\"font-family: Courier New\" value=\"Courier New, Courier, mono\">Courier New</option>";
lblFont 							 += "<option style=\"font-family: Palatino Linotype\" value=\"Palatino Linotype\">Palatino Linotype</option>";
lblFont 							 += "<option style=\"font-family: Times New Roman\" value=\"Times New Roman, Times, serif\">Times New Roman</option>";
lblFont 							 += "<option style=\"font-family: Verdana\" value=\"Verdana, Arial, Helvetica, sans-serif\">Verdana</option>";
// Size Dropdown
var lblSize 					 =  "<option value=\"\">Size</option>";
lblSize 							 += "<option value=\"1\">1</option>";
lblSize 							 += "<option value=\"2\">2</option>";
lblSize 							 += "<option value=\"3\">3</option>";
lblSize 							 += "<option value=\"4\">4</option>";
lblSize 							 += "<option value=\"5\">5</option>";
lblSize 							 += "<option value=\"6\">6</option>";
lblSize 							 += "<option value=\"7\">7</option>";

// Alerts
var lblErrorPreload 	 = "Error preloading content.";
var lblSearchConfirm 	 =  "The search expression [SF] was found [RUNCOUNT] time(s).\n\n"; // Leave in [SF], [RUNCOUNT] and [RW]
lblSearchConfirm			 += "Are you sure you want to replace these entries with [RW] ?\n";
var lblSearchAbort 		 = "Operation Aborted.";
var lblSearchNotFound	 = "was not found.";
var lblCountTotal	     = "Word Count";
var lblCountChar	     = "Available Characters";
var lblCountCharWarn   = "Warning! Your content is too long and may not save correctly.";

// Dialogs
// Insert Link
var lblLinkType			 	 = "Link Type";
var lblLinkOldA				 = "existing anchor";
var lblLinkNewA 	 		 = "new anchor";
var lblLinkNoA     		 = "No Existing Anchors";
var lblLinkAnchors 		 = "Anchors";
var lblLinkAddress		 = "Address";
var lblLinkText		 		 = "Link Text";
var lblLinkOpenIn			 = "Open Link In";
var lblLinkVal0        = "Please enter a url.";
var lblLinkSubmit 		 = "OK";
var lblLinkCancel			 = "Cancel";
// Insert Image
var lblImageURL			 	 = "Image URL";
var lblImageAltText		 = "Alternative Text";
var lblImageBorder 	 	 = "Border";
var lblImageBorderPx 	 = "Pixels";
var lblImageVal0     	 = "Please indicate the \"Image URL\".";
var lblImageSubmit 		 = "OK";
var lblImageCancel		 = "Cancel";
// Insert Table
var lblTableRows			 = "Rows";
var lblTableColumns		 = "Columns";
var lblTableWidth 	 	 = "Table width";
var lblTablePx     		 = "pixels";
var lblTablePercent 	 = "percent";
var lblTableBorder		 = "Border thickness";
var lblTablePadding		 = "Cell padding";
var lblTableSpacing		 = "Cell spacing";
var lblTableSubmit 		 = "OK";
var lblTableCancel		 = "Cancel";
// Search and Replace
var lblSearchFind			 = "Find what";
var lblSearchReplace 	 = "Replace with";
var lblSearchMatch     = "Match case";
var lblSearchWholeWord = "Find whole words only";
var lblSearchVal0			 = "You must enter something into \"Find what:\".";
var lblSearchSubmit		 = "OK";
var lblSearchCancel		 = "Cancel";
// Paste As Plain Text
var lblPasteTextHint   = "Hint: To paste you can either right-click and choose \"Paste\" or use the key combination of Ctrl-V.<br><br>";
var lblPasteTextVal0   = "Please enter text."
var lblPasteTextSubmit = "OK";
var lblPasteTextCancel = "Cancel";
// Paste As Plain Text
var lblPasteWordHint   = "Hint: To paste you can either right-click and choose \"Paste\" or use the key combination of Ctrl-V.<br><br>";
var lblPasteWordVal0   = "Please enter text."
var lblPasteWordSubmit = "OK";
var lblPasteWordCancel = "Cancel";