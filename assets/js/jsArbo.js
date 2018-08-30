$(document).ready(function(){
    var baseURL = window.location.origin;
    function menuContextuel(node){
        var items = {
            "edit":{
                "icon": "fa fa-pencil-alt",
                "label": "Éditer",
                "action": function(){
                    if(node.type === 'root'){//Si Menu
                        idMenu = $('#'+node.id).parents('div').attr('id').substr(5);
                        window.location.href = baseURL+Routing.generate('easyadmin', { action: 'edit', entity: 'Menu', id: idMenu });
                    }else{//Si Page
                        idPage = $('#'+node.id).find('.page').attr('id');
                        window.location.href = baseURL+Routing.generate('easyadmin', { action: 'edit', entity: 'Page_Active', id: idPage });
                    }
                }
            },
            "see":{
                "icon": "fa fa-eye",
                "label": "Voir",
                "action": function(){
                    idPage = $('#'+node.id).find('.page').attr('id');

                    $.ajax({
                        url: baseURL+Routing.generate('urlPage'),
                        method: "post",
                        data: {idPage: idPage}
                    })
                        .done(function(data){
                            window.location.href = baseURL+"/"+data;
                        })
                }
            },
            "create":{
                "icon": "fa fa-plus",
                "label": "Ajouter une page enfant",
                "action": function(){
                    if(node.type === 'root'){//Si Menu
                        idPageParent = null;
                    }else{//Si Page
                        idPageParent = $('#'+node.id).find('.page').attr('id');
                    }
                    idMenu = $('#'+node.id).parents('div').attr('id').substr(5);

                    idMenuComplet = $('#'+node.id).parents('div').attr('id');

                    idLangue = $(".arbo-langues .current a").attr("class");

                    $('#loader-arbo.'+idMenuComplet).fadeIn().html("<i class='fas fa-sync fa-spin'></i>");

                    $.ajax({
                        url: baseURL+Routing.generate('ajouterPageEnfant'),
                        method: "post",
                        data: {idPageParent: idPageParent, idMenu: idMenu, idLangue: idLangue}
                    })
                        .done(function(data){
                            $('#loader-arbo.'+idMenuComplet).fadeIn().html("<i class='fas fa-check'></i>").delay(600).fadeOut();

                            idPage = data.substring(0, data.indexOf('*'));
                            idMenuPage = data.substring(data.indexOf('*')+1);

                            nouveauNode = $('#'+idMenuComplet).jstree("create_node", node, 'Page sans titre<span class="menuPage" id="'+idMenuPage+'"></span><span class="page" id="'+idPage+'"></span>', 'first', false, false);
                        })
                        .fail(function(){
                            $('#loader-arbo.'+idMenuComplet).html("<i class='fas fa-times'></i>").delay(600).fadeOut();
                        });
                }
            },
            "home":{
                "icon": "fa fa-home",
                "label": "Définir comme page d'accueil",
                "action": function(){
                    idPage = $('#'+node.id).find('.page').attr('id');
                    idMenuComplet = $('#'+node.id).parents('div').attr('id');

                    $('#loader-arbo.'+idMenuComplet).fadeIn().html("<i class='fas fa-sync fa-spin'></i>");

                    $.ajax({
                        url: baseURL+Routing.generate('definirPageAccueil'),
                        method: "post",
                        data: {idPage: idPage}
                    })
                        .done(function(data){
                            $('#loader-arbo.'+idMenuComplet).fadeIn().html("<i class='fas fa-check'></i>").delay(600).fadeOut();

                            $('li[data-jstree="{\\"type\\":\\"home\\"}"]').find('svg').removeClass('fa-home').addClass('fa-file');
                            $('li[data-jstree="{\\"type\\":\\"home\\"}"]').attr('data-jstree', '');

                            $('#'+node.id).attr('data-jstree', '{"type":"home"}');
                            $('#'+node.id).find('svg').removeClass('fa-file').addClass('fa-home');

                            //Changement du lien pour éditer la page d'accueil au dessus de l'arbo
                            $('#pageAccueil-menu > li:first-child > a').attr('href', '/admin/?entity=Page_Active&action=edit&id='+idPage);
                            $('.pageAccueil-titre').html(data);
                            if($('.pageAccueil').find('a').hasClass('sansPageAccueil')){
                                $('.pageAccueil p').hide();
                            }
                        })
                        .fail(function(){
                            $('#loader-arbo.'+idMenuComplet).html("<i class='fas fa-times'></i>").delay(600).fadeOut();
                        });
                }
            },
            "alias":{
                "icon": "fa fa-file-export",
                "label": "Créer un raccourci dans...",
                "submenu": {
                }
            },
            "duplicate":{
                "icon": "fa fa-copy",
                "label": "Dupliquer",
                "action": function(){
                    idPage = $('#'+node.id).find('.page').attr('id');
                    window.location.href = baseURL+Routing.generate('easyadmin', { action: 'dupliquer', entity: 'Page_Active', id: idPage });
                }
            },
            "remove":{
                "icon": "fa fa-minus",
                "label": "Retirer du menu",
                "action": function(){
                    idMenuPage = $('#'+node.id).find('.menuPage').attr('id');
                    idMenu = $('#'+node.id).parents('div').attr('id');
                    $('#loader-arbo.'+idMenu).fadeIn().html("<i class='fas fa-sync fa-spin'></i>");

                    $.ajax({
                        url: baseURL+Routing.generate('retirerDuMenu'),
                        method: "post",
                        data: {idMenuPage: idMenuPage}
                    })
                        .done(function(data){
                            $('#loader-arbo.'+idMenu).fadeIn().html("<i class='fas fa-check'></i>").delay(600).fadeOut();
                            idPage = $('#'+node.id).find('.page').attr('id');
                            if(data === "orpheline"){
                                nodeParent = $('#menu-0').jstree("get_node", $('#menu-0 > ul > li'));
                                $('#menu-0').jstree("create_node", nodeParent, node, 'first');
                            }
                            $('#'+idMenu).jstree().delete_node([node.id]);
                        })
                        .fail(function(){
                            $('#loader-arbo.'+idMenu).html("<i class='fas fa-times'></i>").delay(600).fadeOut();
                        });
                }
            },
            "delete":{
                "icon": "fa fa-trash-alt",
                "label": "Supprimer",
                "action": function(){
                    idPage = $('#'+node.id).find('.page').attr('id');
                    window.location.href = baseURL+Routing.generate('easyadmin', { action: 'corbeille', entity: 'Page_Active', id: idPage });
                }
            }
        };

        //Ajout des sous-menus de "Créer un raccourci dans..."
        $('div[id^="menu"]:not("#menu-0")').each(function(){
            id = $(this).attr('id');

            idMenu = $('#'+node.id).closest('.jstree-container-ul').parent('div').attr('id');
            if(idMenu !== id){
                nom = $('#'+id+' > ul > li > a').text();
            }else{
                nom = "Ce menu";
            }

            items.alias.submenu[id] = {
                "icon": "fas fa-folder",
                "label": nom,
                "menu": id.substr(5),
                "menuComplet": id,
                "action": function(sousMenu){//sousMenu = objet "copier dans menu X"
                    idMenu = sousMenu.item.menu;
                    idMenuComplet = sousMenu.item.menuComplet;

                    idAncienMenuComplet = $('#'+node.id).parents('div').attr('id');

                    idPage = $('#'+node.id).find('.page').attr('id');

                    $('#loader-arbo.'+idMenuComplet).fadeIn().html("<i class='fas fa-sync fa-spin'></i>");

                    idAnciendMenuPage = $('#'+node.id).find('.menuPage').attr('id');

                    $.ajax({
                        url: baseURL+Routing.generate('creerAlias'),
                        method: "post",
                        data: {idMenu : idMenu, idPage : idPage, idAncienMenuComplet : idAncienMenuComplet, idAnciendMenuPage : idAnciendMenuPage }
                    })
                        .done(function(data){
                            $('#loader-arbo.'+idMenuComplet).fadeIn().html("<i class='fas fa-check'></i>").delay(600).fadeOut();

                            nodeParent = $('#'+idMenuComplet).jstree("get_node", $('#'+idMenuComplet+' > ul > li'));

                            nouveauNode = $('#'+idMenuComplet).jstree("copy_node", node, nodeParent, 'first', function(node, parent, position){
                                $('#'+node.id).find('.menuPage').attr('id', data);
                            });

                            if(idAncienMenuComplet = 'menu-0'){
                                $('#'+idAncienMenuComplet).jstree().delete_node([node.id]);
                            }
                        })
                        .fail(function(){
                            $('#loader-arbo.'+idMenuComplet).html("<i class='fas fa-times'></i>").delay(600).fadeOut();
                        });
                }
            };
        });

        //On enlève les options inutiles pour les menus
        if (node.type === 'root'){
            delete items.delete;
            delete items.duplicate;
            delete items.remove;
            delete items.alias;
        }

        if($('#'+node.id).closest('div').attr('id') === 'menu-0' && node.type !== 'root'){
            delete items.create;
            delete items.alias;
            delete items.remove;
        }

        return items;
    }

    //Initialisation JSTree
    options = {
        "plugins" : [
            "dnd", "types", "contextmenu"
        ],
        "contextmenu":{
            "items": menuContextuel
        },
        //"state" : { "key" : "menuVertical"},//Pour sauvegarder l'état de l'arbo. Rajouter le plugin state
        "types" : {
            "#" : {
                "valid_children" : ["root"]
            },
            "root" : {
                "icon" : "fas fa-folder"
            },
            "default" : {
                "icon" : "fas fa-file"
            },
            "home" : {
                "icon" : "fas fa-home"
            }
        },
        "core" : {
            "animation" : 0,
            "html_titles" : true,
            "check_callback" : true,
            "themes" : { "stripes" : false }
        },
        "dnd": {
            "is_draggable" : function(nodes, event){
                return(nodes[0].type !== 'root');
            }
        }
    };

    $('.sidebar-menus div[id^="menu"]').jstree(options);//Menus

    enregistrementMenu = function(e, data){
        idMenuComplet = $('#'+data.node.id).parents('div').attr('id');
        $('#loader-arbo.'+idMenuComplet).fadeIn().html("<i class='fas fa-sync fa-spin'></i>");
        arbo = [];
        $(this).jstree('open_node', data.node.parent);
        $(this).find('.jstree-container-ul li').find('li').each(function(){
            position = $(this).index();

            idLi = $(this).attr('id');
            idMenu = $('#'+idLi).parents('div[id^="menu"]').attr('id').substr(5);
            idMenuPage = $('#'+idLi).find('.menuPage').attr('id');
            idPage = $('#'+idLi).find('.page').attr('id');

            if($(this).parent('ul').parent('li').parent('ul').hasClass('jstree-container-ul')){
                idPageParent = null;
            }else{
                idLiParent = $(this).parent('ul').parent('li').attr('id');
                idPageParent = $('#'+idLiParent).find('.page').attr('id');
            }
            menuPage = [idMenuPage, idPage, position, idPageParent, idMenu];

            /*console.log(menuPage);*/

            arbo.push(menuPage);
        });
        $.ajax({
            url: baseURL+Routing.generate('enregistrerMenu'),
            method: "post",
            data: {arbo: arbo}
        })
            .done(function(){
                $('#loader-arbo.'+idMenuComplet).fadeIn().html("<i class='fas fa-check'></i>").delay(600).fadeOut();
            })
            .fail(function(){
                $('#loader-arbo.'+idMenuComplet).fadeIn().html("<i class='fas fa-times'></i>").delay(600).fadeOut();
            });
    };

    $('.sidebar-menus div[id^="menu"]').on('select_node.jstree', function (e, data) {
        setTimeout(function() {
            data.instance.show_contextmenu(data.node)
        }, 100);
    });

    $('.sidebar-menus div[id^="menu"]').on('move_node.jstree copy_node.jstree', enregistrementMenu);

    $('.sidebar-menus div[id^="menu"]').on('create_node.jstree', function(e, data){
        idMenuComplet = $('#'+data.node.id).parents('div').attr('id');
        if(!idMenuComplet === 'menu-0'){
            enregistrementMenu();
        }
    });

    $('.sidebar-menus div[id^="menu"]').on('ready.jstree', function(){
        $(this).jstree().open_all();
//            context_hide();
    });
});