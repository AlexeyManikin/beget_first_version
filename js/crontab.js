function changeEntry(job_id)
{
    var form = document.getElementById('changeJob');
    form.number.value   = job_id;
    form.dows.value     = document.getElementById('dows' + job_id).value;
    form.monthes.value  = document.getElementById('monthes' + job_id).value;
    form.days.value     = document.getElementById('days' + job_id).value;
    form.hours.value    = document.getElementById('hours' + job_id).value;
    form.minutes.value  = document.getElementById('minutes' + job_id).value;
    form.commands.value = encodeURIComponent(document.getElementById('commands' + job_id).value);
    form.actions.value  = 'changeEntry';
    form.submit();
};

function removeEntry(job_id)
{
    var form = document.getElementById('fremoveEntry');
    form.number.value   = job_id;
    form.submit();
};

function addEntry()
{
    var form = document.getElementById('changeJob');
    form.dows.value     = document.getElementById('dows_add').value;
    form.monthes.value  = document.getElementById('monthes_add').value;
    form.days.value     = document.getElementById('days_add').value;
    form.hours.value    = document.getElementById('hours_add').value;
    form.minutes.value  = document.getElementById('minutes_add').value;
    form.commands.value = encodeURIComponent(document.getElementById('commands_add').value);
    form.actions.value  = 'addEntry';
    form.submit();
}; 
