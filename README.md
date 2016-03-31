# proutbot
Bot php perso, stupide et un peux trash pour tester la Telegram Bot API
https://core.telegram.org/bots/api

# @BotFather /setcommands
blague - Fais une blague
bonjourmadame - La madame du jour - Start Nb(20 Max)
tumblr - Start Nb(20 Max) Domain
fact - C'est un fait.
hello - Sois poli, dis bonjour.
butts - Du cul, du cul, du cul !
boobs - Need boobs !
beer - Beer !!!!
pipi - Fais un pipi
vomi - Fais un vomi
prout - Fais un prout
caca - Fais un caca
help - Demandes de l'aide

# Simple lancement manuel via terminal dans le repertoire du bot
watch -n 5 php proutbot.php

# Instalation en tant que service (Ubuntu 15.10) (/data/bots/proutbot/)
sudo vim /etc/systemd/system/proutbot.service

[Unit]
Description=ProutBot

[Service]
ExecStart=/usr/bin/php /data/bots/proutbot/proutbot.php
Restart=always
RestartSec=5

[Install]
WantedBy=multi-user.target


sudo service proutbot start
sudo systemctl status proutbot.service

sudo systemctl enable proutbot
