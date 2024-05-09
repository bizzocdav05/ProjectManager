function set_theme_color(colors) {
    $("html").css("--color-primary", colors[0]);
    $("html").css("--color-secondary", colors[1]);
    $("html").css("--color-tertiary", colors[2]);
    $("html").css("--color-quaternary", colors[3]);
}