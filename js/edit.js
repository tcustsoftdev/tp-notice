  
 
    function getAppUrl(url){
        var appUrl='http://localhost/tp-notices/';  
        return appUrl + url;
    }

    function redirctPage(page){
            document.location = getAppUrl(page);
    }
    function deleteNotice(){
            var url = getAppUrl('delete.php');
            var id=getNoticeId();
            $.ajax({
                type: "POST",
                url: url,
                data:{Id:id},
                dataType: 'json',
                success: function (data) {
                redirctPage('index.php');
                    
                },
                error: function (e) {
                    console.log(e);
                    OnError();
                }
            });
        
    }
    function delAttachment() {
            var url = getAppUrl('delete.php');
            var id=getAttachmentId();
            
            $.ajax({
                type: "POST",
                url: url,
                data:{Attachment_Id:id},
                dataType: 'json',
                success: function (data) {
                    setAttachmentId('0');
                    $("input[name='Attachment_Title']").val('');

                    toggleFile();
                    
                },
                error: function (e) {
                    console.log(e);
                    OnError();
                }
            });

        
    }

    function reviewOK(){
        $('#form-approve').submit();
    }

    function getNoticeId(){
        
        return $('#notice-id').val();
    }


    function canEdit(){
        var val=$('#can-edit').val();
        return isTrue(val);
    }
    function canReview(){
        var val=$('#can-review').val();
        return isTrue(val);
    }
    function canDelete(){
        var val=$('#can-delete').val();
        return isTrue(val);
    }

    function isTrue(val){
        if(!val) return false;
        if(typeof val=='number'){
            return val > 0
        }else if(typeof val=='string'){
            if(val.toLowerCase()=='true') return true
            if(val=='1') return true
            return false
        }else if(typeof val=='boolean'){
            return val
        }
    
        return false
    }
    function setSelectedUnits(unitCodes, unitNames) {
    
        var textCode = '';
        for (var i = 0; i < unitCodes.length; i++) {
            textCode += unitCodes[i];
            if (i < unitCodes.length - 1) textCode += ',';
        }
        $('#unit-codes').val(textCode);

    
    
        var textName = '';
        for (var i = 0; i < unitNames.length; i++) {
            textName += unitNames[i];
            if (i < unitNames.length - 1) textName += ',';
        }


        $('#unit-names').val(textName);

        CloseCustomModal();

        //隱藏err-msg
        $("input[name='Units']").next().hide();
        //$('#unit-list').show();

    }
    
    function setSelectedClasses(codes, names) {

        var textCode = '';
        for (var i = 0; i < codes.length; i++) {
            textCode += codes[i];
            if (i < codes.length - 1) textCode += ',';
        }
        $('#class-codes').val(textCode);



        var textName = '';
        for (var i = 0; i < names.length; i++) {
            textName += names[i];
            if (i < names.length - 1) textName += ',';
        }


        $('#class-names').val(textName);

        CloseCustomModal();

        //隱藏err-msg
        $("input[name='Classes']").next().hide();
        $('#class-list').show();

    }
    function getSelectedUnits() {
        return $('#unit-codes').val();
    }
    function getSelectedClasses() {
        return $('#class-codes').val();
    }

    function fetchUnits() {
        var url = 'http://203.64.35.83:8080/api/units';
        return new Promise((resolve, reject) => {
            $.getJSON(url)
            .done(function (data) {
                createNodeList(data);
                resolve(true)
            }).fail(function (error) {
                reject(error);
            });

        })
    }
    function loadUnitNames() {
        var url = 'http://203.64.35.83:8080/api/units';
        url += '?mode=1';
        var codes = getSelectedUnits();
        
        if (!codes) return;

        var code_array = codes.split(',');		
        
        $.getJSON(url)
        .done(function (data) {
            
            var names = '';
            for (var i = 0; i < data.length; i++) {
                if (code_array.includes(data[i].code)) {
                names +=  data[i].name + ',';
            }
            }
            
            names = names.slice(0, -1);

            $('#unit-names').val(names);

        }).fail(function (error) {
            OnError();
        });
    }
    function fetchClasses() {
        var url = 'http://203.64.35.83:8080/api/classes';
        
        return new Promise((resolve, reject) => {
            $.getJSON(url)
            .done(function (data) {
                createNodeList(data);
                resolve(true)
            }).fail(function (error) {
                reject(error);
            });

        })
    }
    function loadClassNames() {
        var url = 'http://203.64.35.83:8080/api/classes';
        
        var codes = getSelectedClasses();
        
        if (!codes) return;

        var code_array = codes.split(',');		
        
        $.getJSON(url)
        .done(function (data) {
            
            var names = '';
            for (var i = 0; i < data.length; i++) {
                if (code_array.includes(data[i].code)) {
                names +=  data[i].name + ',';
            }
            }
            
            names = names.slice(0, -1);

            $('#class-names').val(names);

        }).fail(function (error) {
            OnError();
        });
    }
    function createNodeList(data) {
    
        var html = '';

        for (var i = 0; i < data.length ; i++) {
            html += getNode(data[i]);
        }

        $("#treeview-members").html(html);
    }
    function getNode(unit) {
        var html = '<li>';

        if (unit.children && unit.children.length) {
            html += ' <i class="fa fa-plus"></i>';
            html += '<label>' + '<input data-name="' + unit.name + '"   data-id="' + unit.code + '"   type="checkbox" />'
            html += unit.name + '</label>';
        } else {
            html += '<label>' + '<input data-name="' + unit.name + '"   data-id="' + unit.code + '" class="hummingbirdNoParent"  type="checkbox" />'
            html += unit.name + '</label>';
        }


        if (unit.children && unit.children.length) {
            html += ' <ul>';
            for (var i = 0; i < unit.children.length ; i++) {
                html += getNode(unit.children[i]);
            }
            html += ' </ul>';
        }

        return html;

    }
    function iniUnitsTree() {
        var treeview = $("#treeview-members");
        var type = getSelectType();
    

        var selected_codes = '';
        if (type == 'unit') {
            selected_codes = getSelectedUnits();
        } else {
        
            selected_codes = getSelectedClasses();
        }

        
    
        treeview.hummingbird();

        if (!selected_codes) return;

        var selected_ids = selected_codes.split(',');
        for (var i = 0; i < selected_ids.length; i++) {
            treeview.hummingbird("checkNode", {
                attr: "data-id",
                name: selected_ids[i],
                expandParents: false
            });
        }
        
        
    }
    function staffChecked() {
        return isTrue($("input[type='checkbox'][name='Staff']").val());
    }

    function teacherChecked() {
        return isTrue($("input[type='checkbox'][name='Teacher']").val());
    }

    function studentChecked() {
        return isTrue($("input[type='checkbox'][name='Student']").val());
    }

    

    function onStaffCheckChanged(checked) {
        
        if (checked) {
            var hideLevels = false;
            beginSelectUnits(hideLevels);
            $('#unit-list').show();
            $('#err-roles').hide();
        } else {
            if (!teacherChecked()) {
                $('#unit-list').hide();
            }
            
        }
    }
    function onTeacherCheckChanged(checked) {
    
        if (checked) {
            var hideLevels = true;
            beginSelectUnits(hideLevels);
            $('#unit-list').show();
            $('#err-roles').hide();
        } else {
            if (!staffChecked()) {
                $('#unit-list').hide();
            }
        }
    }
    function onStudentCheckChanged(checked) {
        if (checked) {
            beginSelectClasses();
            $('#class-list').show();
            $('#err-roles').hide();
        } else {
        
            $('#class-list').hide();
        }
    } 
    function beginSelectUnits(hideLevels) {
        setSelectType('unit');
    
        var units = fetchUnits();

        units.then(result => {
            iniUnitsTree();
            if (hideLevels) {
                $('#div-level').hide();
            } else {
                $('#div-level').show();
            }
            
            ShowCustomModal('請選擇發送對象部門');
        })
        .catch(error=> {
            OnError();
        })
    }

    function beginSelectClasses() {
        setSelectType('class');

        var classes = fetchClasses();

        classes.then(result => {
            iniUnitsTree();
        
            $('#div-level').hide();
            ShowCustomModal('請選擇發送對象班級');
        })
        .catch(error=> {
            OnError();
        })
    }

    
    function setSelectType(type) {
        $('#select-type').val(type)
    }
    function getSelectType() {
        return $('#select-type').val();
    }
    function onSelectDone() {
    
        var type = getSelectType();
        var id_list = [];
        $("#treeview-members").hummingbird("getChecked", {
            attr: "data-id",
            list: id_list,
            OnlyFinalInstance: true
        });

        if (!id_list.length) {
            if (type == 'unit') alert('請選擇部門');
            else alert('請選擇班級');

            return false;
        }

        var name_list = [];
        $("#treeview-members").hummingbird("getChecked", {
            attr: "data-name",
            list: name_list,
            OnlyFinalInstance: true
        });

        

        if (type == 'unit') {
            setSelectedUnits(id_list, name_list);
            
            setLevels();
        } else {
            setSelectedClasses(id_list, name_list);
        }
    

    }

    function setLevels()
    {
        var ids = '';
        if ($('#level-one').prop("checked")) ids = '1';

        if ($('#level-two').prop("checked")) {
            if (ids) ids += ',';

            ids += '2';
        }

        $("input[name='Levels']").val(ids);

        setLevelsText();
    

    }
    function getLevels() {
    return $("input[name='Levels']").val();
    }
    function setLevelsText() {
        var levels = getLevels();
        var text = '';
        if (levels) {
            var lavel_ids = levels.split(',');
            if (lavel_ids.indexOf('1') > -1) text += '一級主管';

            if (lavel_ids.indexOf('2') > -1) {
                if (text) text += ',';
                text += '二級主管';
            }

        }

        if (text) text = ' ( ' + text + ' )';

        $('#level-text').text(text);

    
    
    }
    

    function CloseCustomModal() {
        $('#close-custom-modal').click();
    }

    function ShowCustomModal(title) {
        SetCustomModalTitle(title)
        $('#open-custom-modal').click();
    }

    function SetCustomModalTitle(title) {
        $('#custom-modal-title').text(title);
    }

    function ShowAlert(content,showBtn) {
        $('#alert-content').html(content);

        if (showBtn) {
            $('#alert-footer').show();
        } else {
            $('#alert-footer').hide();
        }
        $('#btn-alert-modal').click();
    }
    function CloseAlert() {
        $('#close-alert').click();
    }


    function OnError() {
        alert('系統發生錯誤, 請稍後再試');
    }


    function clearErrorMsg(target) {
    
        if (target.name == 'Content') {
            var inputContent = $("textarea[name='Content']");
            inputContent.next().hide();

            return;
        }


        var input = $("input[name='" + target.name + "']");
        
        input.next().hide();
    
    }
    function showErrors(msgs) {
        if (!msgs.length) return;
        var html = '<ul>';
        for (var i = 0; i < msgs.length; i++) {
            html += '<li>' + msgs[i] + '</li>';
        
        }

        html += '</ul>';

        var showBtn = false;
        ShowAlert(html, showBtn)
    }

    function getConfirmType() {
        return $('#confirm-action').val();
    }
    function setConfirmType(value) {
        $('#confirm-action').val(value);
    }

    function getAttachmentId() {
        return  $("input[name='Attachment_Id']").val();
    
    }
    function setAttachmentId(value) {
        $("input[name='Attachment_Id']").val(value);
    }

    function onConfirmOK() {
        CloseAlert();

        var type = getConfirmType();
        if (type == 'del-attachment') {
            delAttachment();
        }else if(type == 'delete-notice'){
            deleteNotice();
            
        }else if(type == 'review-ok'){
            reviewOK();
            
        }
    }
    
    



    function toggleFile(){
        
        var attachmentId=getAttachmentId();
        attachmentId=parseInt(attachmentId);
        if(attachmentId > 0){
            $('#attachment-file').hide();
            $('#div-exist-attachment').show();
        }else{
            $('#attachment-file').show();
            $('#div-exist-attachment').hide();
        }
    }

    function chkRoles() {
        $('.chk-roles').each(function () {
            $selected = isTrue($(this).val());
            $(this).prop("checked", $selected);

        });
    }
    function chkLevels() {
        var levels = getLevels();
        if (levels) {
            var lavel_ids = levels.split(',');
            $('.chk-levels').each(function () {
                $selected = lavel_ids.includes($(this).val());               
                $(this).prop("checked", $selected);

            });

            setLevelsText();
        }
        
    }


    function iniEdit() {
    
        if(!canEdit()){
            $("#form-notice :input").attr("disabled", true);
            $('#submit-buttons').hide();
        }
        
        if(!canDelete()){
            $("#btn-delete").hide();
        }
        
        if(!canReview()){
            
            $("#form-approve").hide();
        }
        
        toggleFile();

        chkRoles();
        chkLevels();

        var student = studentChecked();
        var teacher = teacherChecked();
        var staff =staffChecked();

        if (teacher || staff) {
            $('#unit-list').show();
        }

        if (student) {
            $('#class-list').show();
        }

        loadUnitNames();
        loadClassNames();
        
        

    }