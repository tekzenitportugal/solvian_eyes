(function ($) {
	'use strict';

	function nextIndex($tbody) {
		var max = -1;
		$tbody.find('tr').each(function () {
			var name = $(this).find('[name]').first().attr('name') || '';
			var match = name.match(/\[(\d+)\]/);
			if (match) {
				max = Math.max(max, parseInt(match[1], 10));
			}
		});
		return max + 1;
	}

	function bindFilePicker($scope) {
		$scope.find('.eyes-pro-select-file').off('click.eyesPro').on('click.eyesPro', function (event) {
			event.preventDefault();
			var $button = $(this);
			var $row = $button.closest('td');
			var $input = $row.find('.eyes-pro-file-id');
			var $preview = $row.find('.eyes-pro-file-preview');
			var frame = wp.media({
				title: 'Escolher ficheiro',
				button: { text: 'Usar ficheiro' },
				multiple: false
			});

			frame.on('select', function () {
				var attachment = frame.state().get('selection').first().toJSON();
				$input.val(attachment.id);
				$preview.text(attachment.filename || attachment.title || '');
			});

			frame.open();
		});
	}

	function rowHtml(type, index) {
		if (type === 'legal-point') {
			return (
				'<tr>' +
				'<td><input type="text" class="large-text" name="eyes_pro[concurso_legal_points][' + index + '][point]" value="" /></td>' +
				'<td class="eyes-pro-row-actions"><button type="button" class="button-link-delete eyes-pro-remove-row">Remover</button></td>' +
				'</tr>'
			);
		}

		if (type === 'document') {
			return (
				'<tr>' +
				'<td><input type="text" class="large-text" name="eyes_pro[concurso_documents][' + index + '][label]" value="" /></td>' +
				'<td>' +
				'<input type="hidden" class="eyes-pro-file-id" name="eyes_pro[concurso_documents][' + index + '][file]" value="" />' +
				'<button type="button" class="button eyes-pro-select-file">Escolher ficheiro</button>' +
				'<span class="eyes-pro-file-preview"></span>' +
				'</td>' +
				'<td><label><input type="checkbox" name="eyes_pro[concurso_documents][' + index + '][is_complement]" value="1" /> Sim</label></td>' +
				'<td class="eyes-pro-row-actions"><button type="button" class="button-link-delete eyes-pro-remove-row">Remover</button></td>' +
				'</tr>'
			);
		}

		if (type === 'form') {
			return (
				'<tr>' +
				'<td><input type="text" class="large-text" name="eyes_pro[concurso_application_forms][' + index + '][label]" value="" /></td>' +
				'<td>' +
				'<input type="hidden" class="eyes-pro-file-id" name="eyes_pro[concurso_application_forms][' + index + '][file]" value="" />' +
				'<button type="button" class="button eyes-pro-select-file">Escolher ficheiro</button>' +
				'<span class="eyes-pro-file-preview"></span>' +
				'</td>' +
				'<td class="eyes-pro-row-actions"><button type="button" class="button-link-delete eyes-pro-remove-row">Remover</button></td>' +
				'</tr>'
			);
		}

		return (
			'<tr>' +
			'<td><input type="text" class="large-text" name="eyes_pro[concurso_legislation][' + index + '][title]" value="" /></td>' +
			'<td><textarea class="large-text" rows="3" name="eyes_pro[concurso_legislation][' + index + '][summary]"></textarea></td>' +
			'<td class="eyes-pro-row-actions"><button type="button" class="button-link-delete eyes-pro-remove-row">Remover</button></td>' +
			'</tr>'
		);
	}

	$(function () {
		var $box = $('.eyes-pro-admin');
		if (!$box.length) {
			return;
		}

		bindFilePicker($box);

		$box.on('click', '.eyes-pro-add-row', function (event) {
			event.preventDefault();
			var type = $(this).data('row-type');
			var $section = $(this).closest('section');
			var $tbody = $section.find('tbody');
			var index = nextIndex($tbody);
			var $row = $(rowHtml(type, index));
			$tbody.append($row);
			bindFilePicker($row);
		});

		$box.on('click', '.eyes-pro-remove-row', function (event) {
			event.preventDefault();
			$(this).closest('tr').remove();
		});
	});
})(jQuery);
