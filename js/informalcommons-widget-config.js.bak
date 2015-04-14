var icWidget = new InformalcommonsWidget();


icWidget.searchResultRecordRenderer = function (record) {


var title = record.record.resource.title.values[0].value,

url = record.record.icSpecific.recordUrl.values[0].value,

description = record.record.resource.description.values[0].value,

contributors = record.record.resource.contributor.values;


var addHtml = '<div class="icRow">' +

'<div class="icRecordName"><a href="' + url + '" class="record-title" target="_blank">' + title + '</a></div>' +


/*** Description ***/

'<div class="icDescription">' +

'<div class="icTitle">Description:</div>' +

'<div class="icValue">' + description.substring(0, 300) + '...</div>' +

'</div>' ;



/*** Contributor ***/

addHtml += '<div class="icContributors">' +

'<div class="icTitle">Contributor(s):</div>' +

'<div class="icValue">';

var contributorNames = [];

for (var icC = 0; icC < contributors.length; icC++) {

if(contributors[icC].organizationName != undefined) {

var orgName = contributors[icC].organizationName.values[0].value;

if($.inArray(orgName, contributorNames) == -1) {

contributorNames.push(orgName);

}

}

}

addHtml += contributorNames.join(' | ');

addHtml += '</div></div>';



addHtml += '</div>'; // closing tag of icRow



return addHtml;

}



icWidget.searchFormSubmitted = function(form)

{

var term = form.term.value;

this.search({'qq':term, 'icRecordType':''});

}



icWidget.init();

