var file_tables = new Array();

var filetable_fields = [ {name: 'name',    size: '400',     caption: 'Имя'},
                         {name: 'restore', size: '100',   caption: 'Действие'}];

// Получаем поле по имени
function get_field_hash(f_name){
  for(var i in filettable_fields) if (f_name == filettable_fields[i].name)   return filettable_fields[i]
  return 0;
};

// Высчитываем длины столбцов
function count_cell_width(t_width, fields){

  var counters = new Array();
  for(var i in fields){
    var field = get_field_hash(fields[i]);
    if (field.size > 0) counters[i] = field.size; 
    else counters[i] = 450;/*cell_width*/;
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
  for(var i in filetable_fields) if (f_name == filetable_fields[i].name)   return filetable_fields[i];
  return 0;
};

// Получаем таблицу по списку
function get_file_table(name){
  for (var i in file_tables)  
      if (file_tables[i].name == name)  
          return file_tables[i];
};

// Сохраняем таблицу
function save_file_table(name, table){
  for (var i in file_tables)  if (file_tables[i].ft_name == name){
    file_tables[i] = table;
    return;
  };
};

function chdir_file_list(add_dir, callback){
    ajax_change_files_directory(add_dir,
    function(req){
      var directory =  "/" + req.responseText
      document.getElementById("current_dir").value = directory;
      callback(directory);
    }
  );
};

function init_file_table(name, t_width, fields, methods){
  var file_table = {  name:      name, 
                      width:     t_width,
                      fields:    fields,
                      methods:   methods,
                      cur_dir:       '/' };
  file_tables.push(file_table);
  document.write(create_tag("div", "div_ft_" + name, "", "", ""));
};

function changeDate(){
	var new_date = document.getElementById("date").value;
	ajax_set_date(new_date,function(req){}	);
	filetable_chdir('backup_table',document.getElementById("current_dir").value);
}

function changeType(type){
	ajax_set_type(type,function(req){});
	filetable_chdir('backup_table','');
}

function filetable_chdir(name, dir){
  var table = get_file_table(name);
  var cur_dir = String(dir).split("\/");
  var count_dept = cur_dir.length;
  if (cur_dir[count_dept-1] == ".."){
  	  dir = "";
  	  for (i=0;i<(count_dept-2);i++){ 
  	     dir = dir+"\/"+ cur_dir[i]	;
  	  }
  }
  var directory =  "/" + dir;
  var reg = /\/\//g;
  directory = directory.replace(reg, "/");
  dir = dir.replace(reg, "/");
  directory = directory.replace(reg, "/");
  dir = dir.replace(reg, "/");
     
  document.getElementById("current_dir").value = directory;
  
  table.cur_dir = dir;
  reload_file_table(name,dir);
};

function filetable_restore(table,path){
    ajax_restore(path,function(req){
    });
    alert("Отправлен запрос на автоматическое востановленние, приблизительное время востановления около 10 минут");
}

function load_file_list(dir,callback){
  ajax_load_file_list(dir,
    function(req){
      var files = String(req.responseText).split("\n");
      var file_list = new Array();
      for(var i in files){
        var file = files[i];
        var a_file = String(file).split("|");
        var file_name = a_file[1];
        var file_type = a_file[0];
        file_list.push({name: file_name, type: file_type});
      };
      callback(file_list);
    }
  );
}


function reload_file_table(name,dir){
  var table = get_file_table(name);
  var c_width = count_cell_width(table.width, table.fields);

  var h_fields = "";
  for(var i in table.fields){
    var field = get_field_hash(table.fields[i]);
    var width = c_width[i];
    h_fields = h_fields + create_tag("td", "", "class=\"table1\" bgcolor=\"#D5D6D7\" width=\""+width+"\" height=23", "", 
      "&nbsp;&nbsp"+field.caption
    );
  };

  table.methods.get_file_list(dir,
    function(file_list){
      var table = get_file_table(name);
      var files_tag = "";

      if (file_list.length == 1 && file_list.length == 0); else 
      for(f_i in file_list){
        var c_file = file_list[f_i];

        var cells_tag = "";
        for(var i in table.fields){
          var field = get_field_hash(table.fields[i]);

          var caption = "";
          switch(field.name){
            case 'name':
              var img_src = "";
              switch(c_file.type){
                case 'd':
                  img_src = "folder_backup.png";
                  break;
                case 'c':
                  img_src = "folderopen_backup.png";
                  break;
                case 'b':
                  img_src = "database_backup.png";
                  break;
                case 't':
                  img_src = "table_backup.png";
                  break;
                case '-':
                  img_src = "file_backup.png";
                  break;
             };
            var img = create_s_tag("img", "", "border=0 src='/images/" + img_src + "'", c_file.type);
            var cap = c_file.name;
            if ((c_file.type == "d") || (c_file.type == "c") || (c_file.type == "b")){
              img = create_tag("a", "", "href='javascript:filetable_chdir(\""+name+"\", \""+dir+"\/"+c_file.name+"\");'", "", img);
              cap = create_tag("a", "", "href='javascript:filetable_chdir(\""+name+"\", \""+dir+"\/"+c_file.name+"\");'", "", cap);
            };
            caption = create_tag("table", "", "", "", 
              create_tag("tr", "", "align=\"left\"", "",
                create_tag("td", "", "align=\"left\"", "width:20;", img) +
                create_tag("td", "", "align=\"left\"", "", cap)
              )
            );
            break;
          case 'restore':
            var r_name = (c_file.restore_name)?c_file.restore_name:c_file.name;
            if (c_file.name == "..")  caption = "&nbsp;"; 
            else caption = 
              create_tag("a", "", "href='javascript:void(0)' onclick='javascript:filetable_restore(\""+name+"\", \""+dir+"\/"+c_file.name+"\");'", "", "Восстановить");
            break;
          default:
            caption = c_file[field.name];
        };
        cells_tag = cells_tag + create_tag("td", "", "align=\"left\"", "height:23;", caption);
      };
      files_tag = files_tag + create_tag("tr", "", "", "", cells_tag);
    };

    var file_table = create_tag("table", "", "border=0 cellpadding=0 cellspacing=0", "width:500px", 
      create_tag("tr", "", "", "", h_fields) + files_tag
    );

    document.getElementById("div_ft_" + table.name).innerHTML = file_table;
    save_file_table(name, table);
    }
  );
}