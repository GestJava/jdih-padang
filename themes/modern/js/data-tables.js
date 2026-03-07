/**
* Written by: Agus Prawoto Hadi
* Year		: 2022
* Website	: jagowebdev.com
*/

jQuery(document).ready(function () {

	if ($('#data-tables').length) {
		// Destroy DataTable jika sudah ada instance sebelumnya
		if ( $.fn.DataTable.isDataTable('#data-tables') ) {
			$('#data-tables').DataTable().destroy();
		}

		const $setting = $('#dataTables-setting');
		let settings = {};
		if ($setting.length > 0) {
			settings = $.parseJSON($('#dataTables-setting').html());
		}

		const addSettings =
		{
			// "dom":"Bfrtip",
			"buttons": [
				{
					"extend": "copy"
					, "text": "<i class='far fa-copy'></i> Copy"
					, "className": "btn-light me-1"
				},
				{
					"extend": "excel"
					, "title": "Data Laporan"
					, "text": "<i class='far fa-file-excel'></i> Excel"
					, "exportOptions": {
						columns: [1, 2, 3, 4, 5],
						modifier: { selected: null }
					}
					, "className": "btn-light me-1"
				},
				{
					"extend": "pdf"
					, "title": "Data Laporan"
					, "text": "<i class='far fa-file-pdf'></i> PDF"
					, "exportOptions": {
						columns: [1, 2, 3, 4, 5],
						modifier: { selected: null }
					}
					, "className": "btn-light me-1"
				},
				{
					"extend": "csv"
					, "title": "Data Laporan"
					, "text": "<i class='far fa-file-alt'></i> CSV"
					, "exportOptions": {
						columns: [1, 2, 3, 4, 5],
						modifier: { selected: null }
					}
					, "className": "btn-light me-1"
				},
				{
					"extend": "print"
					, "title": "Data Laporan"
					, "text": "<i class='fas fa-print'></i> Print"
					, "exportOptions": {
						columns: [1, 2, 3, 4, 5],
						modifier: { selected: null }
					}
					, "className": "btn-light"
				}

			]
		}

		// Merge settings
		// settings['lengthChange'] = false;
		settings = { ...settings, ...addSettings };

		// settings['buttons'] = [ 'copy', 'excel', 'pdf', 'colvis' ];
		var table = $('#data-tables').DataTable(settings);
		
		// Check if DataTables Buttons extension is loaded
		if (typeof table.buttons === 'function') {
			table.buttons().container()
				.appendTo('#data-tables_wrapper .col-md-6:eq(0)');
		} else {
			console.warn('DataTables Buttons extension not loaded. Export buttons will not be available.');
		}

		// No urut - Dinonaktifkan karena sudah dihandle oleh server-side rendering
		/* table.on('order.dt search.dt', function () {
			table.column(0, { search: 'applied', order: 'applied' }).nodes().each(function (cell, i) {
				cell.innerHTML = i + 1;
			});
		}).draw(); */

	}
});