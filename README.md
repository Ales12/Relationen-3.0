# Relationen-3.0
Dieser Plugin hilft dabei, seine Relationen zu verwalten. Diese werden im Profil angezeigt. Es gibt die Möglichkeit NPCs einzutragen. Die Relationen werden über das User CP verwaltet. Dort können Relationen gelöscht, zurück eingetragen (also man ist wo eingetragen und möchte diesen Charakter bei sich eintragen) und editiert werden. Alle Eintragungen müssen vom eingetragenden Charakter bestätigt werde, erst dann werden sie angezeigt.

## neue Datenbank
- Relationen

## neue Templates
- relations_addtoo 	
- relations_alert 	
- relations_alert_other 	
- relations_edit 	
- relations_form 	
- relations_npc_form 	
- relations_profile 	
- relations_profile_bit 	
- relations_profile_cat 	
- relations_profile_edit 	
- relations_profile_edit_npc 	
- relations_usercp 	
- relations_usercp_bit 	
- relations_usercp_nav

## neue Variabeln
Profil
```
{$relations_profile}
```
Header
```
{$relations_alerts}
```

## neue CSS
relations.css
``` 
.relation_flex{
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
}

.relation_box{
	width: 45%;
	box-sizing: border-box;
	margin: 10px 2px;
}

.npc_relations {  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: min-content min-content min-content min-content; 
  gap: 5px 5px;
  grid-auto-flow: row;
  grid-template-areas:
    "charaname charaname"
    "relationstype shortfacts"
    "relationstext relationstext"
    "relationsubmit relationsubmit";
	width: 50%;
	text-align: center;
	margin: auto;
}


.charaname { grid-area: charaname; }

.relationstype { grid-area: relationstype; }

.shortfacts { grid-area: shortfacts; }

.relationstext { grid-area: relationstext; }

.relationsubmit { grid-area: relationsubmit; }


.relations  {  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-template-rows: min-content min-content min-content;
  gap: 5px 5px;
  grid-auto-flow: row;
  grid-template-areas:
    "relationscat relationskind"
    "relationstext relationstext"
    "relationssubmit relationssubmit";
		width: 50%;
	text-align: center;
	margin: auto;
}

.relationscat { grid-area: relationscat; }

.relationskind { grid-area: relationskind; }

.relationstext { grid-area: relationstext; }

.relationssubmit { grid-area: relationssubmit; }

.npc_relations > div, .relations > div {
	padding: 5px 10px;	
}
.npc_relations .tcat, .relations .tcat{
	margin: 5px 0;	
}

/*profile*/
.profile_box{
	width: 100%;
	margin: 10px;
	padding: 10px;
	box-sizing: border-box;
}

.profile_charaname{
	font-size 15px;
	text-align: center;
	border-bottom: 1px solid;
}

.profile_short{
	font-size: 10px;
	text-align: center;
}

.profile_relatext{
	height:100px;
	width: justify;
	overflow: auto;
	line-height: 1.2em;
}

.profile_avatar{
	height:100px;
	width: auto;
	float: left;
	margin: 2px 5px 0 1px;
}

.profile_avatar img{
	height: 100px;
	width: auto;
	float: left;
	margin: 2px 5px 0 1px;
}

.profile_options{
	width: 100%;
	text-align: center;
}

/*usercp*/

/* Style the tab */
.tab {
  overflow: hidden;
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  float: left;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #ccc;
}

/* Style the tab content */
.tabcontent {
  display: none;
  padding: 6px 12px;
  -webkit-animation: fadeEffect 1s;
  animation: fadeEffect 1s;
}

/* Fade in tabs */
@-webkit-keyframes fadeEffect {
  from {opacity: 0;}
  to {opacity: 1;}
}

@keyframes fadeEffect {
  from {opacity: 0;}
  to {opacity: 1;}
}

.relations_flex{
	display: flex;
	flex-wrap: wrap;
}

.relations_flex .relations_box{
	width: 33%;
	margin: 10px 5px;
	background: #efefef;
	box-sizing: border-box;
	padding: 2px;
}

.relations_username{
	font-size: 15px;
	text-align: center;
	font-weight: bold;
}

.relations_short{
		font-size: 11px;
	text-align: center;
border-bottom: 1px solid #0f0f0f;
	padding: 3px 10px;
	margin-bottom: 2px;
}

.relations_text{
			font-size: 11px;
	text-align: justify;
}

.relations_options{
		font-size: 13px;
	text-align: center;
	padding: 5px;
}
```
