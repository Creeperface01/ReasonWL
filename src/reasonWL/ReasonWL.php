<?php

namespace reasonWL;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\player\PlayerPreLoginEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat;

class ReasonWL extends PluginBase implements Listener{

    private $reason = "";
    
    public function onEnable(){
        //$this->getLogger()->info("ReasonWL enabled");
        $this->getServer ()->getPluginManager ()->registerEvents ($this, $this );
        $this->initConfig();
    }
    
    private function initConfig() {
        $path = $this->getServer()->getDataPath()."whitelist_reason.txt";

	    if(!file_exists($path)){
            $file = fopen($path, 'w');
            fwrite($file, "Server is whitelisted");
            fclose($file);
        }

        $this->reason = str_replace("&", "§", file_get_contents($path));
    }
    
    public function onPreLogin(PlayerPreLoginEvent $e){
        $p = $e->getPlayer();

        if(!$this->getServer()->isWhitelisted($p->getName())){
            $e->setCancelled();
            $e->setKickMessage($this->reason);
        }

    }

    private function reload(){
        $this->initConfig();
    }

    public function onCommand(CommandSender $sender, Command $cmd, string $label, array $args) : bool{
        if(strtolower($cmd->getName()) == "rwl"){
            switch(strtolower($args[0])){
                case "reload":
                    $sender->sendMessage(TextFormat::GRAY."reloading...");
                    $this->reload();
                    $sender->sendMessage(TextFormat::GREEN."reload successful");
                    break;
                default:
                    $sender->sendMessage(TextFormat::YELLOW."Use /rwl reload");
                    break;
            }
        }
    }
}
