let iniciado = document.querySelector('.gestionar_sesion').getAttribute("login");

if (iniciado === "true") {
    document.querySelector(".usuario_img_sidebar").src = "/public/menu_white.png";
    document.querySelector(".usuario_img_navbar").src = "/public/menu_white.png";
    document.querySelector(".iniciar_sesion").style.display = "none";
    document.querySelector(".cerrar_sesion").style.display = "block";

} else {
    document.querySelector(".usuario_img_sidebar").src = "/public/usuario_default.png";
    document.querySelector(".usuario_img_navbar").src = "/public/usuario_default.png";
    document.querySelector(".iniciar_sesion").style.display = "block";
    document.querySelector(".cerrar_sesion").style.display = "none";
}