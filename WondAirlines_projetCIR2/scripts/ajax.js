function printInfo(json) {
    let data = JSON.parse(json);
    let vancouver = document.getElementById('vancouver');
	let toronto = document.getElementById('toronto');
	let quebeccity = document.getElementById('quebeccity');
	let ottawa = document.getElementById('ottawa');
	let montreal = document.getElementById('montreal');
	vancouver.innerHTML = data['vancouver'];
	toronto.innerHTML = data['toronto'];
	quebeccity.innerHTML = data['toronto'];
	ottawa.innerHTML = data['toronto'];
	montreal.innerHTML = data['toronto'];
    setTimeout(ajaxRequest('GET','content/controller.php?func=getTime',printInfo), 1000);
}
function ajaxRequest(type, url, callback) {
    let var1 = new XMLHttpRequest();
    var1.open(type,url);
    var1.onload = () => {
        switch(var1.status) {
            case 200:
            case 201:
                callback(var1.responseText);
            break;
            default:
        }
    };
    var1.send();
}
ajaxRequest('GET','content/controller.php?func=getTime',printInfo);