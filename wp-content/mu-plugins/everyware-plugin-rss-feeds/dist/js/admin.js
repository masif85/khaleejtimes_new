jQuery(document).ready((function(){const e=function(e){return jQuery("input, select, textarea").filter('[name$="['+e+']"]')},t=function(e){let t=null;return e.each((function(e,n){n.checked&&(t=n.value)})),t},n=function(e,t){let n=oc_ajax.oc_ajax_url||"";t.empty().append('<i class="fa fa-spinner fa-spin fa-2x fa-fw"></i>').addClass("oc-test-result--loaded"),jQuery.post(n,e,null,"json").done((function(e){let n,s='<span class="icon icon--ok dashicons dashicons-yes dashicons-yes-alt"></span>',a='<span class="icon icon--warning dashicons dashicons-warning"></span>',o='<span class="icon icon--error dashicons dashicons-warning"></span>',r=e.responseSubject,c=e.response;switch(e.responseType){case"OK":n=s;break;case"WARNING":n=a;break;case"ERROR":n=o;break;default:n=o,r="Unknown response type"}let i="<p><strong>"+n+r+":</strong> "+c+"</p>";t.empty().append(i)}))};jQuery('<button type="button" class="button button-small hide-if-no-js js-copy-permalink">Copy URL</button>').appendTo("#edit-slug-buttons").before(" "),jQuery(document).on("click",".js-copy-permalink",(function(e){e.preventDefault(),function(e){let t=jQuery("<input>");jQuery("body").append(t),t.val(jQuery(e).text()).select(),document.execCommand("copy"),t.remove()}("#sample-permalink")}));let s=e("link_type");const a=function(){let n="page"===t(s);e("link_page").attr("disabled",!n).attr("required",n),e("link_url").attr("disabled",n).attr("required",!n)};s.change(a);let o=e("feed_source");const r=function(){let n="list"===t(o);jQuery(".js-feed_source_type_list").toggle(n),jQuery(".js-feed_source_type_query").toggle(!n),e("feed_source_list").attr("required",n),e("feed_source_query").attr("required",!n),e("feed_source_query_sorting").attr("required",!n)};o.change(r);let c=e("has_image");const i=function(){let t=c.attr("checked");e("image_width").attr("disabled",!t)};c.change(i);e("feed_source_list").change((function(e){let t=jQuery(e.target).closest(".js-feed_source_type_list"),s={action:"validate_oc_list",uuid:t.find(".js-oc-list").val()},a=t.find(".js-oc-test-result");n(s,a)}));jQuery(".js-oc-query-test").click((function(e){e.preventDefault();let t=jQuery(e.target).closest(".js-feed_source_type_query"),s={action:"validate_oc_query",query:t.find(".js-oc-query").val(),start:t.find(".js-oc-query-start").val(),limit:t.find(".js-oc-query-limit").val()},a=t.find(".js-oc-test-result");n(s,a)})),a(),r(),i()}));
//# sourceMappingURL=data:application/json;charset=utf8;base64,eyJ2ZXJzaW9uIjozLCJzb3VyY2VzIjpbImFkbWluLmpzIl0sIm5hbWVzIjpbImpRdWVyeSIsImRvY3VtZW50IiwicmVhZHkiLCJnZXRGaWVsZHMiLCJuYW1lIiwiZmlsdGVyIiwiZ2V0UmFkaW9WYWx1ZSIsImVsZW1lbnRzIiwicmVzdWx0IiwiZWFjaCIsImkiLCJlbCIsImNoZWNrZWQiLCJ2YWx1ZSIsImFqYXhWYWxpZGF0ZU9jIiwicGFyYW1zIiwiJHJlc3VsdFdyYXBwZXIiLCJwb3N0VXJsIiwib2NfYWpheCIsIm9jX2FqYXhfdXJsIiwiZW1wdHkiLCJhcHBlbmQiLCJhZGRDbGFzcyIsInBvc3QiLCJkb25lIiwiZGF0YSIsImljb24iLCJpY29ucyIsInN1YmplY3QiLCJyZXNwb25zZVN1YmplY3QiLCJtZXNzYWdlIiwicmVzcG9uc2UiLCJyZXNwb25zZVR5cGUiLCJhcHBlbmRUbyIsImJlZm9yZSIsIm9uIiwiZXZlbnQiLCJwcmV2ZW50RGVmYXVsdCIsInNlbGVjdG9yIiwiJHRlbXAiLCJ2YWwiLCJ0ZXh0Iiwic2VsZWN0IiwiZXhlY0NvbW1hbmQiLCJyZW1vdmUiLCJjb3B5VG9DbGlwYm9hcmQiLCIkbGlua190eXBlcyIsIm9uTGlua1R5cGVDaGFuZ2UiLCJsaW5rSXNQYWdlIiwiYXR0ciIsImNoYW5nZSIsIiRmZWVkX3NvdXJjZXMiLCJvbkZlZWRTb3VyY2VDaGFuZ2UiLCJzb3VyY2VJc0xpc3QiLCJ0b2dnbGUiLCIkaGFzX2ltYWdlIiwib25IYXNJbWFnZUNoYW5nZSIsImhhc0ltYWdlIiwid3JhcHBlciIsInRhcmdldCIsImNsb3Nlc3QiLCJhY3Rpb24iLCJ1dWlkIiwiZmluZCIsImNsaWNrIiwicXVlcnkiLCJzdGFydCIsImxpbWl0Il0sIm1hcHBpbmdzIjoiQUFBQUEsT0FBT0MsVUFBVUMsT0FBTSxXQU9uQixNQUFNQyxFQUFZLFNBQVVDLEdBQ3hCLE9BQU9KLE9BQU8sMkJBQTJCSyxPQUFPLFlBQWNELEVBQU8sUUFTbkVFLEVBQWdCLFNBQVVDLEdBQzVCLElBQUlDLEVBQVMsS0FNYixP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Q29udGVudCI6WyJqUXVlcnkoZG9jdW1lbnQpLnJlYWR5KGZ1bmN0aW9uICgpIHtcclxuICAgIC8qKlxyXG4gICAgICogRmluZCBmb3JtIGZpZWxkcyBieSBuYW1lLCB3aXRob3V0IGJlaW5nIGNvbmZ1c2VkIGJ5IHByZWZpeGVzLlxyXG4gICAgICpcclxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBuYW1lXHJcbiAgICAgKiBAcmV0dXJucyB7alF1ZXJ5fVxyXG4gICAgICovXHJcbiAgICBjb25zdCBnZXRGaWVsZHMgPSBmdW5jdGlvbiAobmFtZSkge1xyXG4gICAgICAgIHJldHVybiBqUXVlcnkoJ2lucHV0LCBzZWxlY3QsIHRleHRhcmVhJykuZmlsdGVyKCdbbmFtZSQ9XCJbJyArIG5hbWUgKyAnXVwiXScpO1xyXG4gICAgfTtcclxuXHJcbiAgICAvKipcclxuICAgICAqIEZpbmQgdGhlIGNoZWNrZWQgdmFsdWUgaW4gYSBjb2xsZWN0aW9uIG9yIHJhZGlvIGlucHV0cy5cclxuICAgICAqXHJcbiAgICAgKiBAcGFyYW0ge2pRdWVyeX0gZWxlbWVudHMgLSBDb2xsZWN0aW9uIG9mIHJhZGlvIGlucHV0cy4gU2hvdWxkIGFsbCBoYXZlIHRoZSBzYW1lIG5hbWUuXHJcbiAgICAgKiBAcmV0dXJucyB7c3RyaW5nfG51bGx9XHJcbiAgICAgKi9cclxuICAgIGNvbnN0IGdldFJhZGlvVmFsdWUgPSBmdW5jdGlvbiAoZWxlbWVudHMpIHtcclxuICAgICAgICBsZXQgcmVzdWx0ID0gbnVsbDtcclxuICAgICAgICBlbGVtZW50cy5lYWNoKGZ1bmN0aW9uIChpLCBlbCkge1xyXG4gICAgICAgICAgICBpZiAoZWwuY2hlY2tlZCkge1xyXG4gICAgICAgICAgICAgICAgcmVzdWx0ID0gZWwudmFsdWU7XHJcbiAgICAgICAgICAgIH1cclxuICAgICAgICB9KTtcclxuICAgICAgICByZXR1cm4gcmVzdWx0O1xyXG4gICAgfTtcclxuXHJcbiAgICAvKipcclxuICAgICAqIERvIEFqYXggYW5kIHByaW50IGEgcmVzcG9uc2UgbWVzc2FnZS5cclxuICAgICAqXHJcbiAgICAgKiBAcGFyYW0ge29iamVjdH0gcGFyYW1zXHJcbiAgICAgKiBAcGFyYW0ge2pRdWVyeX0gJHJlc3VsdFdyYXBwZXJcclxuICAgICAqL1xyXG4gICAgLypnbG9iYWwgb2NfYWpheCovXHJcbiAgICBjb25zdCBhamF4VmFsaWRhdGVPYyA9IGZ1bmN0aW9uIChwYXJhbXMsICRyZXN1bHRXcmFwcGVyKSB7XHJcbiAgICAgICAgbGV0IHBvc3RVcmwgPSBvY19hamF4Lm9jX2FqYXhfdXJsIHx8ICcnO1xyXG5cclxuICAgICAgICAkcmVzdWx0V3JhcHBlci5lbXB0eSgpLmFwcGVuZCgnPGkgY2xhc3M9XCJmYSBmYS1zcGlubmVyIGZhLXNwaW4gZmEtMnggZmEtZndcIj48L2k+JykuYWRkQ2xhc3MoJ29jLXRlc3QtcmVzdWx0LS1sb2FkZWQnKTtcclxuXHJcbiAgICAgICAgalF1ZXJ5LnBvc3QocG9zdFVybCwgcGFyYW1zLCBudWxsLCAnanNvbicpXHJcbiAgICAgICAgICAgIC5kb25lKGZ1bmN0aW9uIChkYXRhKSB7XHJcbiAgICAgICAgICAgICAgICBsZXQgaWNvbnMgPSB7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIC8vIHRvZG8gcmVtb3ZlIGRhc2hpY29ucy15ZXMgb25jZSBkYXNoaWNvbnMteWVzLWFsdCBpcyBhdmFpbGFibGVcclxuICAgICAgICAgICAgICAgICAgICAgICAgb2s6ICc8c3BhbiBjbGFzcz1cImljb24gaWNvbi0tb2sgZGFzaGljb25zIGRhc2hpY29ucy15ZXMgZGFzaGljb25zLXllcy1hbHRcIj48L3NwYW4+JyxcclxuICAgICAgICAgICAgICAgICAgICAgICAgd2FybmluZzogJzxzcGFuIGNsYXNzPVwiaWNvbiBpY29uLS13YXJuaW5nIGRhc2hpY29ucyBkYXNoaWNvbnMtd2FybmluZ1wiPjwvc3Bhbj4nLFxyXG4gICAgICAgICAgICAgICAgICAgICAgICBlcnJvcjogJzxzcGFuIGNsYXNzPVwiaWNvbiBpY29uLS1lcnJvciBkYXNoaWNvbnMgZGFzaGljb25zLXdhcm5pbmdcIj48L3NwYW4+JyxcclxuICAgICAgICAgICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAgICAgICAgIHN1YmplY3QgPSBkYXRhLnJlc3BvbnNlU3ViamVjdCxcclxuICAgICAgICAgICAgICAgICAgICBtZXNzYWdlID0gZGF0YS5yZXNwb25zZSxcclxuICAgICAgICAgICAgICAgICAgICBpY29uO1xyXG4gICAgICAgICAgICAgICAgc3dpdGNoIChkYXRhLnJlc3BvbnNlVHlwZSkge1xyXG4gICAgICAgICAgICAgICAgICAgIGNhc2UgJ09LJzpcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWNvbiA9IGljb25zLm9rO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuICAgICAgICAgICAgICAgICAgICBjYXNlICdXQVJOSU5HJzpcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWNvbiA9IGljb25zLndhcm5pbmc7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIGJyZWFrO1xyXG4gICAgICAgICAgICAgICAgICAgIGNhc2UgJ0VSUk9SJzpcclxuICAgICAgICAgICAgICAgICAgICAgICAgaWNvbiA9IGljb25zLmVycm9yO1xyXG4gICAgICAgICAgICAgICAgICAgICAgICBicmVhaztcclxuICAgICAgICAgICAgICAgICAgICBkZWZhdWx0OlxyXG4gICAgICAgICAgICAgICAgICAgICAgICBpY29uID0gaWNvbnMuZXJyb3I7XHJcbiAgICAgICAgICAgICAgICAgICAgICAgIHN1YmplY3QgPSAnVW5rbm93biByZXNwb25zZSB0eXBlJztcclxuICAgICAgICAgICAgICAgICAgICAgICAgYnJlYWs7XHJcbiAgICAgICAgICAgICAgICB9XHJcblxyXG4gICAgICAgICAgICAgICAgbGV0IHJlc3VsdCA9ICc8cD48c3Ryb25nPicgKyBpY29uICsgc3ViamVjdCArICc6PC9zdHJvbmc+ICcgKyBtZXNzYWdlICsgJzwvcD4nO1xyXG5cclxuICAgICAgICAgICAgICAgICRyZXN1bHRXcmFwcGVyLmVtcHR5KCkuYXBwZW5kKHJlc3VsdCk7XHJcbiAgICAgICAgICAgIH0pO1xyXG4gICAgfTtcclxuXHJcbiAgICAvKipcclxuICAgICAqIENvcHkgYSBzZWxlY3RvcidzIGVsZW1lbnQncyB0ZXh0IGNvbnRlbnQgdG8gY2xpcGJvYXJkLlxyXG4gICAgICpcclxuICAgICAqIEBwYXJhbSB7c3RyaW5nfSBzZWxlY3RvclxyXG4gICAgICovXHJcbiAgICBjb25zdCBjb3B5VG9DbGlwYm9hcmQgPSBmdW5jdGlvbiAoc2VsZWN0b3IpIHtcclxuICAgICAgICBsZXQgJHRlbXAgPSBqUXVlcnkoJzxpbnB1dD4nKTtcclxuICAgICAgICBqUXVlcnkoJ2JvZHknKS5hcHBlbmQoJHRlbXApO1xyXG4gICAgICAgICR0ZW1wLnZhbChqUXVlcnkoc2VsZWN0b3IpLnRleHQoKSkuc2VsZWN0KCk7XHJcbiAgICAgICAgZG9jdW1lbnQuZXhlY0NvbW1hbmQoJ2NvcHknKTtcclxuICAgICAgICAkdGVtcC5yZW1vdmUoKTtcclxuICAgIH1cclxuXHJcbiAgICAvLyBBZGQgYSBidXR0b24gdGhhdCwgd2hlbiBjbGlja2VkLCBjb3BpZXMgY3VycmVudCBwZXJtYWxpbmsgdG8gY2xpcGJvYXJkLlxyXG4gICAgalF1ZXJ5KCc8YnV0dG9uIHR5cGU9XCJidXR0b25cIiBjbGFzcz1cImJ1dHRvbiBidXR0b24tc21hbGwgaGlkZS1pZi1uby1qcyBqcy1jb3B5LXBlcm1hbGlua1wiPicgKyAnQ29weSBVUkwnICsgJzwvYnV0dG9uPicpXHJcbiAgICAgICAgLmFwcGVuZFRvKCcjZWRpdC1zbHVnLWJ1dHRvbnMnKVxyXG4gICAgICAgIC5iZWZvcmUoJyAnKTtcclxuICAgIC8vIChUaGUgZXZlbnQgbmVlZHMgdG8gYmUgYm91bmQgdG8gYGRvY3VtZW50YCwgYmVjYXVzZSB0aGUgYnV0dG9uIGl0c2VsZiBpcyByZW1vdmVkL3JlY3JlYXRlZCBmcm9tIEhUTUwgd2hlbiBzYXZpbmcgYW4gZWRpdCB0byB0aGUgc2x1Zy4pXHJcbiAgICBqUXVlcnkoZG9jdW1lbnQpLm9uKCdjbGljaycsICcuanMtY29weS1wZXJtYWxpbmsnLCBmdW5jdGlvbiAoZXZlbnQpIHtcclxuICAgICAgICBldmVudC5wcmV2ZW50RGVmYXVsdCgpO1xyXG4gICAgICAgIGNvcHlUb0NsaXBib2FyZCgnI3NhbXBsZS1wZXJtYWxpbmsnKTtcclxuICAgIH0pO1xyXG5cclxuICAgIC8vIEVuYWJsZS9kaXNhYmxlL3JlcXVpcmUgZmllbGRzIGRlcGVuZGluZyBvbiBjaG9zZW4gbGluayB0eXBlLlxyXG4gICAgbGV0ICRsaW5rX3R5cGVzID0gZ2V0RmllbGRzKCdsaW5rX3R5cGUnKTtcclxuICAgIGNvbnN0IG9uTGlua1R5cGVDaGFuZ2UgPSBmdW5jdGlvbiAoKSB7XHJcbiAgICAgICAgbGV0IGxpbmtJc1BhZ2UgPSAoZ2V0UmFkaW9WYWx1ZSgkbGlua190eXBlcykgPT09ICdwYWdlJyk7XHJcbiAgICAgICAgZ2V0RmllbGRzKCdsaW5rX3BhZ2UnKS5hdHRyKCdkaXNhYmxlZCcsICFsaW5rSXNQYWdlKS5hdHRyKCdyZXF1aXJlZCcsIGxpbmtJc1BhZ2UpO1xyXG4gICAgICAgIGdldEZpZWxkcygnbGlua191cmwnKS5hdHRyKCdkaXNhYmxlZCcsIGxpbmtJc1BhZ2UpLmF0dHIoJ3JlcXVpcmVkJywgIWxpbmtJc1BhZ2UpO1xyXG4gICAgfTtcclxuICAgICRsaW5rX3R5cGVzLmNoYW5nZShvbkxpbmtUeXBlQ2hhbmdlKTtcclxuXHJcbiAgICAvLyBIaWRlL3Nob3cvcmVxdWlyZSBmaWVsZHMgZGVwZW5kaW5nIG9uIGNob3NlbiBmZWVkIHNvdXJjZS5cclxuICAgIGxldCAkZmVlZF9zb3VyY2VzID0gZ2V0RmllbGRzKCdmZWVkX3NvdXJjZScpO1xyXG4gICAgY29uc3Qgb25GZWVkU291cmNlQ2hhbmdlID0gZnVuY3Rpb24gKCkge1xyXG4gICAgICAgIGxldCBzb3VyY2VJc0xpc3QgPSAoZ2V0UmFkaW9WYWx1ZSgkZmVlZF9zb3VyY2VzKSA9PT0gJ2xpc3QnKTtcclxuICAgICAgICBqUXVlcnkoJy5qcy1mZWVkX3NvdXJjZV90eXBlX2xpc3QnKS50b2dnbGUoc291cmNlSXNMaXN0KTtcclxuICAgICAgICBqUXVlcnkoJy5qcy1mZWVkX3NvdXJjZV90eXBlX3F1ZXJ5JykudG9nZ2xlKCFzb3VyY2VJc0xpc3QpO1xyXG4gICAgICAgIGdldEZpZWxkcygnZmVlZF9zb3VyY2VfbGlzdCcpLmF0dHIoJ3JlcXVpcmVkJywgc291cmNlSXNMaXN0KTtcclxuICAgICAgICBnZXRGaWVsZHMoJ2ZlZWRfc291cmNlX3F1ZXJ5JykuYXR0cigncmVxdWlyZWQnLCAhc291cmNlSXNMaXN0KTtcclxuICAgICAgICBnZXRGaWVsZHMoJ2ZlZWRfc291cmNlX3F1ZXJ5X3NvcnRpbmcnKS5hdHRyKCdyZXF1aXJlZCcsICFzb3VyY2VJc0xpc3QpO1xyXG4gICAgfTtcclxuICAgICRmZWVkX3NvdXJjZXMuY2hhbmdlKG9uRmVlZFNvdXJjZUNoYW5nZSk7XHJcblxyXG4gICAgLy8gRW5hYmxlL2Rpc2FibGUgaW1hZ2Ugd2lkdGggZmllbGQgZGVwZW5kaW5nIG9uIGltYWdlIGNoZWNrYm94LlxyXG4gICAgbGV0ICRoYXNfaW1hZ2UgPSBnZXRGaWVsZHMoJ2hhc19pbWFnZScpO1xyXG4gICAgY29uc3Qgb25IYXNJbWFnZUNoYW5nZSA9IGZ1bmN0aW9uICgpIHtcclxuICAgICAgICBsZXQgaGFzSW1hZ2UgPSAkaGFzX2ltYWdlLmF0dHIoJ2NoZWNrZWQnKTtcclxuICAgICAgICBnZXRGaWVsZHMoJ2ltYWdlX3dpZHRoJykuYXR0cignZGlzYWJsZWQnLCAhaGFzSW1hZ2UpO1xyXG4gICAgfTtcclxuICAgICRoYXNfaW1hZ2UuY2hhbmdlKG9uSGFzSW1hZ2VDaGFuZ2UpO1xyXG5cclxuICAgIC8vIEFqYXggY2hlY2sgaG93IG1hbnkgYXJ0aWNsZXMgdGhlIHNlbGVjdGVkIGxpc3QgaGFzLlxyXG4gICAgY29uc3Qgb25MaXN0Q2hhbmdlID0gZnVuY3Rpb24gKGV2ZW50KSB7XHJcbiAgICAgICAgbGV0IHdyYXBwZXIgPSBqUXVlcnkoZXZlbnQudGFyZ2V0KS5jbG9zZXN0KCcuanMtZmVlZF9zb3VyY2VfdHlwZV9saXN0JyksXHJcbiAgICAgICAgICAgIHBhcmFtcyA9IHtcclxuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3ZhbGlkYXRlX29jX2xpc3QnLFxyXG4gICAgICAgICAgICAgICAgdXVpZDogd3JhcHBlci5maW5kKCcuanMtb2MtbGlzdCcpLnZhbCgpLFxyXG4gICAgICAgICAgICB9LFxyXG4gICAgICAgICAgICAkcmVzdWx0V3JhcHBlciA9IHdyYXBwZXIuZmluZCgnLmpzLW9jLXRlc3QtcmVzdWx0Jyk7XHJcblxyXG4gICAgICAgIGFqYXhWYWxpZGF0ZU9jKHBhcmFtcywgJHJlc3VsdFdyYXBwZXIpO1xyXG4gICAgfTtcclxuICAgIGdldEZpZWxkcygnZmVlZF9zb3VyY2VfbGlzdCcpLmNoYW5nZShvbkxpc3RDaGFuZ2UpO1xyXG5cclxuICAgIC8vIEFqYXggY2hlY2sgaG93IG1hbnkgYXJ0aWNsZXMgYSBxdWVyeSBnZW5lcmF0ZXMuXHJcbiAgICBjb25zdCBvblF1ZXJ5VGVzdENsaWNrID0gZnVuY3Rpb24gKGV2ZW50KSB7XHJcbiAgICAgICAgZXZlbnQucHJldmVudERlZmF1bHQoKTtcclxuICAgICAgICBsZXQgd3JhcHBlciA9IGpRdWVyeShldmVudC50YXJnZXQpLmNsb3Nlc3QoJy5qcy1mZWVkX3NvdXJjZV90eXBlX3F1ZXJ5JyksXHJcbiAgICAgICAgICAgIHBhcmFtcyA9IHtcclxuICAgICAgICAgICAgICAgIGFjdGlvbjogJ3ZhbGlkYXRlX29jX3F1ZXJ5JyxcclxuICAgICAgICAgICAgICAgIHF1ZXJ5OiB3cmFwcGVyLmZpbmQoJy5qcy1vYy1xdWVyeScpLnZhbCgpLFxyXG4gICAgICAgICAgICAgICAgc3RhcnQ6IHdyYXBwZXIuZmluZCgnLmpzLW9jLXF1ZXJ5LXN0YXJ0JykudmFsKCksXHJcbiAgICAgICAgICAgICAgICBsaW1pdDogd3JhcHBlci5maW5kKCcuanMtb2MtcXVlcnktbGltaXQnKS52YWwoKSxcclxuICAgICAgICAgICAgfSxcclxuICAgICAgICAgICAgJHJlc3VsdFdyYXBwZXIgPSB3cmFwcGVyLmZpbmQoJy5qcy1vYy10ZXN0LXJlc3VsdCcpO1xyXG5cclxuICAgICAgICBhamF4VmFsaWRhdGVPYyhwYXJhbXMsICRyZXN1bHRXcmFwcGVyKTtcclxuICAgIH07XHJcbiAgICBqUXVlcnkoJy5qcy1vYy1xdWVyeS10ZXN0JykuY2xpY2sob25RdWVyeVRlc3RDbGljayk7XHJcblxyXG4gICAgLy8gSW5pdGlhbGl6ZS5cclxuICAgIG9uTGlua1R5cGVDaGFuZ2UoKTtcclxuICAgIG9uRmVlZFNvdXJjZUNoYW5nZSgpO1xyXG4gICAgb25IYXNJbWFnZUNoYW5nZSgpO1xyXG59KTtcclxuIl19