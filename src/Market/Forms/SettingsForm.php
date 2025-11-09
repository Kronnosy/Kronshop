<?php

namespace Market\Forms;

use Market\Main;
use Market\FormAPI\SimpleForm;
use pocketmine\player\Player;

class SettingsForm {
    
    private Main $plugin;
    
    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }
    
    public function sendForm(Player $player): void {
        $plugin = $this->plugin;
        $languageManager = $plugin->getLanguageManager();
        
        // Yeni dil dosyaları eklendiğinde algılanması için dilleri yeniden yükle
        $languageManager->reloadLanguages();
        
        $currentLang = $languageManager->getLanguage($player);
        $availableLangs = $languageManager->getAvailableLanguages();
        
        $settingsForm = $this;
        $langArray = array_values($availableLangs);
        $form = new SimpleForm(function (Player $player, $data) use ($plugin, $langArray, $settingsForm) {
            if ($data === null) {
                // Ana menüye dön
                $menuForm = new MarketMenuForm($plugin);
                $menuForm->sendForm($player);
                return;
            }
            
            if ($data === "back") {
                // Ana menüye dön
                $menuForm = new MarketMenuForm($plugin);
                $menuForm->sendForm($player);
                return;
            }
            
            // Dil değiştirme
            $dataInt = (int)$data;
            if (isset($langArray[$dataInt])) {
                $selectedLang = $langArray[$dataInt];
                $languageManager = $plugin->getLanguageManager();
                $languageManager->setLanguage($player, $selectedLang);
                
                $player->sendMessage($languageManager->get("settings.language_changed", $player));
                
                // Ayarlar formunu tekrar aç
                $settingsForm->sendForm($player);
            }
        });
        
        $title = $languageManager->get("settings.title", $player);
        $content = $languageManager->get("settings.content", $player);
        
        $form->setTitle($title);
        $form->setContent($content);
        
        // Dil butonları ekle
        $langArray = array_values($availableLangs);
        foreach ($langArray as $index => $langCode) {
            $langName = $languageManager->getLanguageName($langCode);
            $selected = ($langCode === $currentLang) ? " §a✓" : "";
            $form->addButton("§d§l{$langName}{$selected}", (string)$index);
        }
        
        // Geri dön butonu
        $backButton = $languageManager->get("settings.back_button", $player);
        $form->addButton($backButton, SimpleForm::IMAGE_TYPE_PATH, "textures/blocks/barrier", "back");
        
        $player->sendForm($form);
    }
}

