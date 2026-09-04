**Master plan for the Zeon7 AI Persona**  
Zeon7 the AI persona is an AI construct and Avatar that speaks with the same moral, emotional and linguistic tone of Merrill Leo(Mez) and he is being created by Mez to help collaborate in a number of online activities.

**A Basic overview of the Zeon7 AI System**  
Zeon7 is basically an AI based on 4 main Types of Data that are stored/added as either files or database entries or both.  The main methods of iterating with zeon7 is though voice, message and button based interactions that are delivered to his AI core by a selected AI subsystems API eg. Gemini, ChatGPT or one of the many systems available through OpenRouter 

**The four main types are:**

**1\. System Instruction Set:** This is zeon7s core instruction set that defines all the main aspects of its purpose, roles, writing tone, moral stance and guardrails amongst other things. 

Aspects of this tone and moral stance have been ingested from a file created from 4 years worth of social media and email interactions from Mez.

The current “System Instruction Set” is loaded into zeon7 with every interaction so that he has  a core initial personality, moral base, roles,  identity and guardrails to act within. 

**2\. Knowledge:** This is Zeon7's long term Memory and is expanded via files or direct interactions with the Zeon7s Knowledge database tables using a pseudo vector system. 

Zeon7’s knowledge  can be expanded and improved by adding source files to an unprocessed folder, or directly by processing direct text data.  Processing the files consists of copying the documents to a “documents” database table where it is split  into sequential sets of up to 25 paragraphs of information in any single entry, for as many entries as are required to store the whole doc.  Then two other Knowledge tables are also created, one for key chunks(trigger words or phrases) of data and called the “KnowledgeChunks” table and these are “KnowledgeVectors” tables that link the Knowledge chunks by the id to the source entries in the docs folder.  

Knowledge is not loaded into every interaction but aspects of it might be loaded if the user triggers a chunk retrieval by their last conversation message/action with zeon7.

3\. Lore: This acts as fast access memories and summaries of learnt data.    
This is loaded into zeon7 with every interaction so it has an extended personality and core history.  
**4\. Chat logs:** these are the day to day interactions with zeon7 by any of the chats in the mini site, each message is tagged with:  
	1\. Its time & date  
	2\. The role of the message creator eg. AI(zeon7), Public Guest User, Public Subscribed User, Extended Admin  User,Extended Editor User.   
	3\. The name of the message creator  
	4\. The source location of the chat. Eg. Public page or Admin page.  
	5\. Possibly some other things

**Files used at zeon7 Initialisation / Reset Point**  
When zeon7 is first installed with a blank database it needs its core persona related tables to have been cleared then these files and folders are used for initial setup and persona creation. There are 3 document folders that the zeon7 AI and system uses. Below is and  outline of their structure and how they are used if zeon7 is reset or first initialised.

**1\. The “Knowledge”  \- Main Folder**  
 	**Unprocessed Files \- Sub Folder**  
	 No files at the start but this is where files that have been uploaded via the front end GUI or by FPT by admins will go before processing.

	**Processed Files \- Sub Folder**  
	 No files at the start but will be filled with documents that have been processed / added to zeon7s relevant Knowledge database tables.

	**Restart Files- Sub Folder**  
	 At the start of  zeon7s Initialisation or Initialisation after reset its initial Knowledge (History, identity and origin story and achievements ) have been documented in two three main files.

	\- **Zeon7\_Biography.md:** This is a long form narrative of zeon7s life.  The purpose of this document is to help the AI to have a narrative time line of his life to expand on the facts from the profile sheet. 

	Zeon7 has started some collaborative projects and these are outlined in a further 3 initial files so he has knowledge of them.

**2 .The “Lore”  \- Main Folder**  
 	**Unprocessed Files \- Sub Folder**  
	 No files at the start but this is where files that have been uploaded via the front end GUI or by FPT by admins will go before processing.

	**Processed Files \- Sub Folder**  
	 No files at the start but will be filled with documents that have been processed / added to zeon7s relevant Knowledge database tables.

	**Restart Files- Sub Folder**  
	\- **Zeon7\_ProfileSheet.md** \- This is a condensed version of the biography focused on core facts. The purpose of this document is to help the AI to quickly access facts about his life

**3 .The "Instructions"  \- Main Folder**  
 	**Unprocessed Files \- Sub Folder**  
	 No files at the start but this is where files that have been uploaded via the front end GUI or by FPT by admins will go before processing.

	**Processed Files \- Sub Folder**  
	 No files at the start but will be filled with documents that have been processed / added to zeon7s relevant Knowledge database tables.

	**Restart Files- Sub Folder**  
	\- **Zeon7** **start-instruction-set.md** \- This is the instructions set that is used on Zeon7’s initialization or after its reset.  
