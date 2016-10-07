var phis_fields = new Array('family', 'name', 'patronymic', 'fio_english', 'pp_series', 'pp_num', 'pp_date', 'pa_address', 'birth_date', 'adress', 'tel', 'email', 'line_3', 'line_4', 'line_5');
var yur_fields = new Array('company', 'company_eng', 'inn', 'jur_addr', 'phis_address', 'o_tel', 'o_email', 'line_1', 'line_2');

function dropDomain(domain)
{
    if (!confirm("Вы действительно хотите удалить домен " + domain + "?")) return;
    var form = document.getElementById("actionForm");
    form.id.value  = domain;
	form.actions.value = "dropDomain";
    form.submit();
}

function createNewPerson()
{
    var form = document.getElementById("actionForm");
	form.actions.value = "createPerson";
    form.submit();
}

function editPerson()
{
    var form = document.getElementById("actionForm");
	form.actions.value = "createPerson";
	form.id.value      = document.getElementById("person").value;
    form.submit();
}

function cancelCreate()
{
    var form = document.getElementById("idPersonForm");
	form.actions.value = "";
    form.submit();
}

function save_person()
{
    var form = document.getElementById("idPersonForm");
    form.submit();
}

function checkDomain(){
	var domain = document.getElementById("domain").value;
	if (domain.length == 0)
	{
	    document.getElementById("domainStatus").innerHTML = 'Введите доменое имя';
	    document.getElementById("domainStatus").style.color = 'red';
	    return false;
	}

	if (!check_domain(domain))
	{
	    document.getElementById("domainStatus").innerHTML = 'Неккоректное доменое имя';
	    document.getElementById("domainStatus").style.color = 'red';
	    return false;
	}

	var return_date = false;
	var tld     = document.getElementById("tld").value;

	ajax_check_domain(domain,tld,function(req){
	    var ret = req.responseText;
	    if (ret == 1){
	        return_date = true;
	        document.getElementById("domainStatus").innerHTML = 'Домен свободен';
	        document.getElementById("domainStatus").style.color = 'green';
	    }
	    else
	    {
	        return_date = false;
	        document.getElementById("domainStatus").innerHTML = 'Домен занят';
	        document.getElementById("domainStatus").style.color = 'red';
	    }
	}
	);
	return 	return_date;

}

function orderDomain()
{
    var form = document.getElementById("actionForm");
	form.actions.value = "regDomain";
	form.id.value      = document.getElementById("person").value;
	form.domain.value  = document.getElementById("domain").value;
	form.tld.value     = document.getElementById("tld").value;

	if (form.id.value == -1)
	{
	    alert('Выберите пожалуйста персону');
	    return;
	}

	domain = new String(form.domain.value);
	if (domain.length == 0)
	{
	    alert('Введите пожалуйста доменое имя');
	    return;
	}

	if (!check_domain(form.domain.value))
	{
	    alert('Доменное имя может состоять только из букв латинского алфавита, цифр и символов подчеркивания и тире');
	    return;
	}


	if (checkDomain())
	{
	    alert('К сожалению данный домен уже занят');
	    return;
	}


    form.submit();
}

function show_fields(f_array){
  for (var i = 0; i < f_array.length; i++){
    var element = document.getElementById('tr_' + f_array[i]);
    if (element != null)    element.style.display = '';
  };
}

function hide_fields(f_array){
  for (var i = 0; i < f_array.length; i++){
    var element = document.getElementById('tr_' + f_array[i]);
    if (element != null)    element.style.display = 'none';
  };
}

function change_type(){
  PersonForm = document.getElementById('idPersonForm');

  if (PersonForm.type.value == 'person'){
    show_fields(phis_fields);
    hide_fields(yur_fields);

    document.getElementById('fs_person_info').style.height = '413px';
  } else {
    show_fields(yur_fields);
    hide_fields(phis_fields);

    document.getElementById('fs_person_info').style.height = '290px';

  }
}