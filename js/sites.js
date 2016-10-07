function error_message(id)
{
    switch(id)
    {
    case '0':
        break;
    case '1':
        alert('Ошибка при обращении к удаленному серверу, обратитесь в службу поддеждки support@beget.ru');
        break;
    case '2':
        alert('Вы привысили максимально допустимое количество сайтов для вашего тарифного плана.');
        break;
    default:
        alert('Ошибка сервера (code:' + id + '), обратитесь в службу поддеждки support@beget.ru');
    }
}

function get_count_sites()
{
    ajax_get_count_site(function(req){
        var returnArray = String(req.responseText);
        document.getElementById("count_sites").innerHTML = returnArray;
    });
}

function get_sites()
{
    ajax_get_sites(function(req){
        var returnArray = String(req.responseText).split("\n");
        var site = document.getElementById("sites");
        site.options.length = 0;
        for(var i in returnArray){
          var element = returnArray[i];
          if (String(element).length > 0){
            var a_list = String(element).split("|");
            var name   = a_list[0];
            var id     = a_list[1];
            site.options[site.options.length] = new Option(name, id);
          };
        };
    });
}

function get_free_domains()
{
    ajax_get_free_domain(function(req){
        var returnArray = String(req.responseText).split("\n");
        var site = document.getElementById("domains");
        site.options.length = 0;
        for(var i in returnArray){
          var element = returnArray[i];
          if (String(element).length > 0){
            var a_list = String(element).split("|");
            var name   = a_list[0];
            var id     = a_list[1];
            site.options[site.options.length] = new Option(name, id);
          };
        };
    });
}

function unlinkDomain(id, domain)
{
    if (!confirm("Вы действительно хотите отлинковать домен " + domain + "?")) return;
    ajax_unlink_domain(id,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              get_free_domains();
              setTimeout("reload_table('sites_table');", 5);
              alert('Домен успешно отлинкова');
           } else
           {
              error_message(return_value);
           }
           
        });
}

function createSite()
{
    var path = document.getElementById("site_path").value;
    ajax_create_site(path,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              get_sites();
              get_count_sites();
              setTimeout("reload_table('sites_table');", 5);
              alert('Сайт успешно создан');
           } else
           {
              error_message(return_value);
           }
           
        });
}

function dropSites(id, site)
{
    if (!confirm("Вы действительно хотите удалить сайт " + site + "?")) return;
    ajax_drop_sites(id,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              get_count_sites();
              get_sites();
              setTimeout("reload_table('sites_table');", 5);
              alert('Сайт успешно удален');
           } else
           {
              error_message(return_value);
           }
           
        });
}

function linkDomain()
{
    var site   = document.getElementById("sites").value;
    var domain = document.getElementById("domains").value;
    ajax_link_domain(domain,site,function(req){
           var return_value = String(req.responseText);
           if (return_value == '0')
           {
              get_count_sites();
              get_sites();
              get_free_domains();
              setTimeout("reload_table('sites_table');", 5);
              alert('Сайт успешно прилинкован');
           } else
           {
              error_message(return_value);
           }
           
        });
}

var tables = new Array();

var table_fields = [ {name: 'folder',  size: '250',   caption: 'Директория'},
                     {name: 'site',    size: '200',   caption: 'Сайт'},
                     {name: 'do',      size: '100',   caption: 'Действие'}];

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
        if (String(element).length > 0){
          var a_list = String(element).split("|");
          var type   = a_list[0]
          var folder = a_list[1];
          var domain = a_list[2];
          var id     = a_list[3];
          if (type.length > 0){
              list.push({type: type, folder: folder, domain: domain, id: id});
          };
        };
      };
      callback(nametable, list);
    }
  );
};

function reload_table(name)
{
  var table = get_table(name);
  var c_width = count_cell_width(table.width, table.fields);

  var h_fields = "";
  for(var i in table.fields){
    var field = get_field_hash(table.fields[i]);
    var width = c_width[i];
    h_fields = h_fields + create_tag("td", "", "class=\"tableheader\" width=\""+width+"\"", "", 
      create_tag("center", "", "", "", field.caption));
  };

  table.methods.get_list(name,
    function(name,list){
      var table = get_table(name);
      var files_tag = "";
      var flag = '0';

      if (list.length == 0); else 
      for(f_i in list){
        var c_returned = list[f_i];

        var cells_tag = "";
        for(var i in table.fields){
          var field = get_field_hash(table.fields[i]);

          var caption = "";
          switch(field.name){
            case 'folder':
                if (c_returned.type == 'd'){
                    if (c_returned.domain > 0){
                        caption = create_tag("strong", "", "", "color:green;", "&nbsp;/" + c_returned.folder + " (" + c_returned.domain + ")");
                    } else {
                        caption = create_tag("strong", "", "", "", "&nbsp;/" + c_returned.folder + " (0)");
                    }
                }
                break;
            case 'site':
                if (c_returned.type != 'd'){
                    caption = create_tag("div", "", "", "", "&nbsp;&nbsp;&nbsp;www." + c_returned.domain);
                }
                break;
             case 'do':
                if (c_returned.type == 'd'){
                    caption = create_tag("center", "", "", "", create_tag("a", "", "href=\"#\" onclick=\"javascript:dropSites('"+c_returned.id+"','" + c_returned.folder + "');return false;\"", "",
                        create_s_tag("img", "", "src=\"images/del.png\" border=\"0\" alt=\"Удалить сайт " + c_returned.folder + "\" title=\"Удалить сайт " + c_returned.folder + "\"", ""))
                    );
                } else {
                   caption = create_tag("center", "", "", "", create_tag("a", "", "href=\"#\" onclick=\"javascript:unlinkDomain('"+c_returned.id+"','" + c_returned.domain + "');return false;\"", "",
                        create_s_tag("img", "", "src=\"images/del.png\" border=\"0\" alt=\"Отлинковать домен " +c_returned.domain+ "\" title=\"Отлинковать домен " +c_returned.domain+ "\"", ""))
                    ); 
                }
                break;
            };
          cells_tag = cells_tag + create_tag("td", "", "align=\"left\" ", "", caption);
        };
        if (c_returned.type == 'd')
            files_tag = files_tag + create_tag("tr", "domain_table_"+ c_returned.name, "class=\"tabledata1\"", "", cells_tag);
        else
            files_tag = files_tag + create_tag("tr", "domain_table_"+ c_returned.name, "class=\"tabledata2\"", "", cells_tag);
    };

    var file_table = create_tag("table", "", "border=0 cellpadding=1 cellspacing=1", "", 
      create_tag("tr", "", "", "", h_fields) + files_tag
    );

    document.getElementById("div_ft_" + table.name).innerHTML = file_table;
    save_table(name, table);
    }
  );
}
 
