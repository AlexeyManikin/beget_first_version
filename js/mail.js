
function changeDomainFromMail()
{
    var new_domain = document.getElementById("domain").value;
    ajax_set_domain(new_domain,function(req){});
    setTimeout("getDomainMail();", 50);
    setTimeout("reload_table('mail_table');", 50);
}

function getDomainMail()
{
    ajax_get_domain_mail(function(req){
      var files = String(req.responseText);
      var new_domain = document.getElementById("domainMail");
      new_domain.value = files;
    });
}

function setDomainMail()
{
    var new_domain_mail = document.getElementById("domainMail").value;
    ajax_set_domain_mail(new_domain_mail,function(req){
      var return_value = String(req.responseText);
      if (return_value == '1'){
          alert('Почта домена успешно сменена');
      } else
      {
          document.getElementById("domainMail").value = '';
          alert('Ошибка при смене почты домена');
      }
    });
}

function addMailBox()
{
    var mail   = document.getElementById("MailName").value;
    var passwd = document.getElementById("MailPasswd").value;
    ajax_create_mail(mail,passwd,function(req){
      var return_value = String(req.responseText);
      if (return_value == '1'){
        alert('Почтовый ящик успешно создан');
      } else
      {
        alert('Ошибка при создании почтового ящика');
      }});
    setTimeout("reload_table('mail_table');", 50);
}

function dropMail(name)
{
    var domain = document.getElementById("domain").value;
    if (!confirm("Вы действительно хотите удалить почтовый ящик " + name + "@" + domain + " ?")) return;
    ajax_drop_mail(name,function(req){
      var return_value = String(req.responseText);
      if (return_value == '1'){
        alert('Почтовый ящик успешно удален');
      } else
      {
        alert('Ошибка при удалении почтового ящика');
      }});
    setTimeout("reload_table('mail_table');", 50);
    return false;
}


function changePasswd(name)
{
    var new_domain = document.getElementById("domain").value;
    var text       = "Введите пароль для почтового ящика "+name+"@"+new_domain;
    var new_passwd = window.prompt(text,"");
    if (new_passwd != null)
    {
    ajax_change_passwd(name,new_passwd,function(req){
      var return_value = String(req.responseText);
      if (return_value == '1'){
        alert('Пароль почтового ящика успешно удален');
      } else
      {
        alert('Ошибка при смене пароля почтового ящика');
      }});
    }

    setTimeout("reload_table('mail_table');", 50);
    return false;
}


var tables = new Array();

var table_fields = [ {name: 'name', size: '350',   caption: 'Имя почтового ящика'},
                     {name: 'do',   size: '150',   caption: 'Действия'}];

// Получаем поле по имени
function get_field_hash(f_name){
  for(var i in table_fields)
      if (f_name == table_fields[i].name)
         return table_fields[i]
  return 0;
};

// Высчитываем длины столбцов
function count_cell_width(t_width, fields){

  var counters = new Array();
  for(var i in fields){
    var field = get_field_hash(fields[i]);
    if (field.size > 0) counters[i] = field.size; 
    else counters[i] = 100;
  };
  return counters;
};

function create_tag(tagname, id, options, style, inner){
  return "<" + tagname + ((id == "")?"":" id=\""+id+"\"") + ((options == "")?"":" "+options+"") + ((style == "")?"":" style=\""+style+"\"") + ">"+inner + "</" +tagname+">";
};

function create_s_tag(tagname, id, options, style){
  return "<" + tagname + ((id == "")?"":" id=\""+id+"\"") + ((options == "")?"":" "+options+"") + ((style == "")?"":" style=\""+style+"\"") + "/>";
};

// Получаем поле по имени
function get_field_hash(f_name){
  for(var i in table_fields)
      if (f_name == table_fields[i].name)
          return table_fields[i];
  return 0;
};

// Получаем таблицу по списку
function get_table(name){
  for (var i in tables)  
      if (tables[i].name == name)  
          return tables[i];
};

// Сохраняем таблицу
function save_table(name, table){
  for (var i in tables)
    if (tables[i].ft_name == name){
      tables[i] = table;
      return;
  };
};


function init_table(name, t_width, fields, methods){
  var table = {  name:      name, 
                      width:     t_width,
                      fields:    fields,
                      methods:   methods
                      };
  tables.push(table);
  document.write(create_tag("div", "div_ft_" + name, "", "", ""));
};


function load_list(nametable,callback){
  ajax_load_list(
    function(req){
      var returnArray = String(req.responseText).split("\n");
      var list = new Array();
      for(var i in returnArray){
        var element = returnArray[i];
        var a_list = String(element).split("|");
        var name   = a_list[0];
        var domain = a_list[1];
        if (name.length > 0){
            list.push({name: name, domain: domain});
        }
      };
      callback(nametable, list);
    }
  );
}


function reload_table(name)
{
  var table = get_table(name);
  var c_width = count_cell_width(table.width, table.fields);

  var h_fields = "";
  for(var i in table.fields){
    var field = get_field_hash(table.fields[i]);
    var width = c_width[i];
    h_fields = h_fields + create_tag("td", "", "class=\"table\" bgcolor=\"#D5D6D7\" width=\""+width+"\" height=23", "", 
      create_tag("center", "", "", "", field.caption));
  };

  table.methods.get_list(name,
    function(name,list){
      var table = get_table(name);
      var files_tag = "";

      if (list.length == 0); else 
      for(f_i in list){
        var c_file = list[f_i];

        var cells_tag = "";
        for(var i in table.fields){
          var field = get_field_hash(table.fields[i]);

          var caption = "";
          switch(field.name){
            case 'name':
              var img = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" + create_tag("a", "", "href=\"#\"", "", c_file.name + "@" + c_file.domain);
              caption = img;
              break;
            case 'do':
              var deletetag    = create_tag("center", "", "", "",
                                  create_tag("a", "", " href='javascript:void(0)' onclick=\"javascript:dropMail('" + c_file.name + "');\"", "",
                                   create_s_tag("img", "", "src='images/del.png' border = '0' alt='Удалить почтовый ящик'", "")             
                                   )
                                 );
              var changetag    = create_tag("center", "", "", "",
                                  create_tag("a", "", "href='javascript:void(0)' onclick=\"javascript:changePasswd('" + c_file.name + "');\"", "",
                                            create_s_tag("img", "", "src='images/edit.png' border = '0' alt='Изенить пароль'", "") )
                                 );
              caption = create_tag("center", "", "", "", create_tag("table", "", "", "", 
              create_tag("tr", "", "align=\"center\"", "",
                create_tag("td", "", "align=\"center\"", "width:20;", deletetag) +
                create_tag("td", "", "align=\"center\"", "width:20;", "") +
                create_tag("td", "", "align=\"center\"", "width:20;", changetag)
              )));
              break;
           };
          cells_tag = cells_tag + create_tag("td", "", "align=\"left\"", "height:23;", caption);
        };
        files_tag = files_tag + create_tag("tr", "mail_table_"+c_file.name, "bgcolor=\"#fcfcfc\" ", "", cells_tag);
    };

    var file_table = create_tag("table", "", "border=0 cellpadding=1 cellspacing=1 bgcolor=\"#fcfcfc\"", "width:500px", 
      create_tag("tr", "", "", "", h_fields) + files_tag
    );

    document.getElementById("div_ft_" + table.name).innerHTML = file_table;
    save_table(name, table);
    }
  );
}