function registerMember() {
    const data = {
        name: $('#new_name').val(),
        phone: $('#new_phone').val(),
        email: $('#new_email').val(),
        address: $('#new_address').val()
    };
    
    if(!data.name) return alert('กรุณากรอกชื่อลูกค้า');

    $.post('ajax_register_member.php', data, function(res) {
        if(res.success) {
            // เพิ่ม Option ใหม่เข้าไปใน Select2 โดยใช้ ID จริงจาก DB (เช่น 1001)
            const newOption = new Option(res.name + ' (' + res.phone + ')', res.id, true, true);
            $('#cust_select').append(newOption).trigger('change');
            
            // อัปเดตค่าที่จะส่งไปบันทึกการขาย
            $('#h_cust_id').val(res.id); 
            $('#display_cust').text(res.name);
            
            $('#t_member').prop('checked', true);
            $('#mem_wrap').show();

            // ล้างฟอร์ม
            $('#new_name, #new_phone, #new_email, #new_address').val('');
            alert('ลงทะเบียนสำเร็จ! รหัสลูกค้าใหม่คือ: ' + res.id);
        } else {
            alert('เกิดข้อผิดพลาด: ' + res.message);
        }
    }, 'json');
}