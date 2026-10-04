#!/bin/sh
# ========================================================
# SISTEMA DE CALIFICACIONES - SERVIDOR TABLET (POCO / ANDROID)
# ========================================================

# 1. Otorgar permisos de almacenamiento en Termux si es necesario
if [ ! -d "$HOME/storage" ]; then
    termux-setup-storage 2>/dev/null
fi

# 2. Localizar la carpeta del proyecto en cualquier ruta de Android
find_folder() {
    if [ -f "./router.php" ]; then
        pwd
        return 0
    fi

    for path in \
        "/storage/emulated/0/vuela/sis_calif" \
        "/storage/emulated/0/Download/vuela/sis_calif" \
        "/storage/emulated/0/Download/sis_calif" \
        "/storage/emulated/0/sis_calif" \
        "/sdcard/vuela/sis_calif" \
        "/sdcard/Download/sis_calif" \
        "/sdcard/sis_calif" \
        "$HOME/sis_calif"
    do
        if [ -d "$path" ] && [ -f "$path/router.php" ]; then
            echo "$path"
            return 0
        fi
    done

    FOUND=$(find /storage/emulated/0/ /sdcard/ $HOME/ -name "router.php" 2>/dev/null | head -n 1)
    if [ -n "$FOUND" ]; then
        dirname "$FOUND"
        return 0
    fi

    return 1
}

SRC_DIR=$(find_folder)

if [ -z "$SRC_DIR" ]; then
    echo "========================================================"
    echo " [ERROR] No se encontro la carpeta 'sis_calif'."
    echo " Asegurate de copiar la carpeta sis_calif a la memoria de la tablet."
    echo "========================================================"
    exit 1
fi

# 3. Copiar/Sincronizar a la memoria interna privada de Termux (Garantiza permisos 100% en Xiaomi/POCO)
WORK_DIR="$HOME/sis_calif"
if [ "$SRC_DIR" != "$WORK_DIR" ]; then
    echo "[1/4] Copiando archivos a memoria interna del sistema..."
    mkdir -p "$WORK_DIR"
    cp -rf "$SRC_DIR/"* "$WORK_DIR/" 2>/dev/null
fi

cd "$WORK_DIR" || exit 1

clear
echo "========================================================"
echo "    SISTEMA DE CALIFICACIONES VUELA - POCO / TABLET"
echo "========================================================"
echo " [OK] Carpeta detectada y cargada en: $WORK_DIR"
echo "========================================================"
echo ""

# 4. Verificar PHP y OpenSSH
echo "[2/4] Verificando PHP y SSH..."
if ! command -v php >/dev/null 2>&1 || ! command -v ssh >/dev/null 2>&1; then
    echo "[!] Instalando PHP y SSH en Termux..."
    pkg update -y && pkg install -y php openssh curl wget
fi

# 5. Limpiar procesos anteriores
pkill -9 -f "php" >/dev/null 2>&1
sleep 1

# 6. Iniciar Servidor PHP en puerto 8080
echo "[3/4] Iniciando Servidor PHP..."
php -S 0.0.0.0:8080 router.php >/dev/null 2>&1 &
PHP_PID=$!
disown $PHP_PID 2>/dev/null
sleep 2

LOCAL_IPS=$(ifconfig 2>/dev/null | grep -E 'inet ' | grep -v '127.0.0.1' | awk '{print $2}' | sed 's/addr://')

echo ""
echo "--------------------------------------------------------"
echo " 📌 ENLACE LOCAL (Red WiFi / Esta Tablet):"
echo " 👉 http://127.0.0.1:8080"
if [ -n "$LOCAL_IPS" ]; then
    for ip in $LOCAL_IPS; do
        echo " 👉 http://$ip:8080"
    done
fi
echo "--------------------------------------------------------"
echo ""

# 7. Abrir navegador en la tablet principal
if command -v termux-open-url >/dev/null 2>&1; then
    termux-open-url "http://127.0.0.1:8080"
fi

echo "[4/4] GENERANDO ENLACE PUBLICO GLOBAL..."
echo "--------------------------------------------------------"
echo " Comparte por internet la URL HTTPS que termina en .lhr.life"
echo "--------------------------------------------------------"
echo ""

# 8. Tunel SSH
ssh -o StrictHostKeyChecking=no -R 80:127.0.0.1:8080 nokey@localhost.run
