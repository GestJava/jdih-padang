/**
 * Data Agenda Admin JS – server-side DataTables
 */
(function($){
    'use strict';

    function initAgendaDataTable(selector){
        if (!$.fn.DataTable) {
            console.error('DataTables lib not found');
            return;
        }
        if ($.fn.DataTable.isDataTable(selector)) {
            $(selector).DataTable().destroy();
            $(selector).find('tbody').empty();
        }
        $(selector).DataTable({
            processing:true,
            serverSide:true,
            ajax:{
                url: base_url + 'data_agenda/ajax_list',
                type:'POST',
                data:function(d){
                    d[csrf_token_name] = csrf_token_value;
                },
                dataSrc:function(json){
                    console.log('Agenda AJAX success',json);
                    return json.data;
                }
            },
            order:[[2,'desc']],
            columnDefs:[
                {targets:[0,6],orderable:false,searchable:false},
                {targets:[6],render:function(data){return data;}}
            ],
            language:{
                processing:'Memuat...',
                emptyTable:'Tidak ada data agenda'
            }
        });
    }

    $(document).ready(function(){
        if ($('body').hasClass('data-agenda-page') && $('#data-tables').length){
            initAgendaDataTable('#data-tables');
        }
        $(document).on('click','[data-action="reload-table"]',function(e){
            e.preventDefault();
            if ($.fn.DataTable.isDataTable('#data-tables')){
                $('#data-tables').DataTable().ajax.reload();
            }
        });
        // delete handler
        $(document).on('click','[data-action="delete-data"]',function(){
            var id=$(this).data('id');
            var title=$(this).data('delete-title');
            if(!confirm(title)) return;
            $.post(base_url+'data_agenda/delete',{id:id,[csrf_token_name]:csrf_token_value},function(res){
                if(res.success){
                    $('#data-tables').DataTable().ajax.reload();
                }else{
                    alert(res.error||'Gagal menghapus');
                }
            },'json');
        });
    });
})(jQuery); 