<?php

namespace reasonWL;

use pocketmine\utils\TextFormat;
use pocketmine\Player;
use pocketmine\Entity;
use pocketmine\Server;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;
use pocketmine\utils\Config;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;

class Main extends PluginBase implements Listener{
    
    public function onEnable(){
        $this->getLogger()->info("reasonWL enabled");
        $this->getServer ()->getPluginManager ()->registerEvents ($this, $this );
        $this->initConfig();
    }
    
    public function onDisable(){
        $this->getLogger()->info("reasonWL disabled");
    }
    
    private function initConfig() {
	try {
            $this->saveDefaultConfig ();
            if (! file_exists ( $this->getDataFolder () )) {
                @mkdir ( $this->getDataFolder (), 0777, true );
		file_put_contents ( $this->getDataFolder () . "config.yml", $this->getResource ( "config.yml" ) );
                file_put_contents ( $this->getDataFolder () . "players.yml", $this->getResource ( "players.yml" ) );
		}
            $this->reloadConfig ();
            $this->getConfig ()->getAll ();			
            } 
        catch ( \Exception $e ) {
            $this->getLogger ()->error ( $e->getMessage());
	}
    }
    
    public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
        if($sender->isOp() || $sender instanceof ConsoleCommandSender){
            if(strtolower($cmd->getName()) == "rwl" && !isset($args[2])){
                if(isset($args[0])){
                    switch(strtolower($args[0])){
                        case "add":
                            if(!isset($args[1])){
                                $sender->sendMessage(TextFormat::RED."/rwl add [player name]");
                                break;
                            }
                            if($this->addPlayer($args[1]) === false) $sender->sendMessage(TextFormat::RED."player is already whitelisted");
                            else{
                                $this->addPlayer($args[1]);
                                $sender->sendMessage(TextFormat::GREEN."added ".$args[1]." to the whitelist");
                            }
                            break;
                        case "remove":
                            if(!isset($args[1])){
                                $sender->sendMessage(TextFormat::RED."/rwl remove [player name]");
                                break;
                            }
                            if($this->removePlayer($args[1]) === false) $sender->sendMessage(TextFormat::RED."player is not whitelisted");
                            else{
                                $this->removePlayer($args[1]);
                                $sender->sendMessage(TextFormat::GREEN."removed ".$args[1]." from whitelist");
                            }
                            break;
                        case "on":
                            $this->turnWl("on");
                            $sender->sendMessage(TextFormat::GREEN."Whitelist truned on");
                            break;
                        case "off":
                            $this->turnWl("off");
                            $sender->sendMessage(TextFormat::GREEN."Whitelist truned off");
                            break;
                    }
                    if($args[0] !== "add" && $args[0] !== "remove" && $args[0] !== "off" && $args[0] !== "on") $sender->sendMessage(TextFormat::RED."/rwl add [player name]");
                }
                else{
                    $sender->sendMessage(TextFormat::RED."/rwl add [player name]");
                }
            }
        }
    }
    
    public function onJoin(PlayerLoginEvent $e){
        $player = $e->getPlayer();
        if(strtolower($this->getConfig()->get("enable_whitelist")) == "true"){
            if($player->isOp() || $player->hasPermission("rwl.acces") || $this->isWhitelisted($player)){
                return;
            }
            else{
                $player->kick(str_replace("&", "§", $this->getConfig()->get("reason")), false);
            }
        }
    }
    
    public function addPlayer($player){
        $cfg = new Config($this->getDataFolder()."players.yml", Config::YAML);
        if($cfg->getNested("Players.".strtolower($player)) != "true"){
            $cfg->setNested("Players.".strtolower($player), "true");
        }
        $cfg->save();
        $cfg->reload();
    }
    
    public function removePlayer($player){
        $cfg = new Config($this->getDataFolder()."players.yml", Config::YAML);
        if($cfg->getNested("Players.".strtolower($player)) != "false"){
            $cfg->setNested("Players.".strtolower($player), "false");
        }
        $cfg->save();
        $cfg->reload();
    }
    
    public function isWhitelisted(Player $player){
        $cfg = new Config($this->getDataFolder()."players.yml", Config::YAML);
        if($cfg->getNested("Players.".strtolower($player->getName())) == "true"){
            return true;
        }
        return false;
    }
    
    public function turnWl($on){
        $cfg = new Config($this->getDataFolder()."players.yml", Config::YAML);
        if(strtolower($on) == "on"){
            $this->getConfig()->set("enable_whitelist", "true");
        }
        if(strtolower($on) == "off"){
            $this->getConfig()->set("enable_whitelist", "false");
        }
        $cfg->reload();
    }
}