<?php

namespace ARPG\entity\projectile;

use ARPG\entity\ProjectileEntity;
use pocketmine\Player;
use pocketmine\network\protocol\AddEntityPacket;

use ARPG\entity\projectile\ABlazeFireball;

class ADragonFireBall extends ABlazeFireball{

	const NETWORK_ID = 79;
	
 public function spawnTo(Player $player){
  
  $pk = new AddEntityPacket();
  $pk->eid = $this->getId();
  $pk->type = self::NETWORK_ID;
  $pk->x = $this->x;
  $pk->y = $this->y;
  $pk->z = $this->z;
  $pk->speedX = $this->motionX;
  $pk->speedY = $this->motionY;
  $pk->speedZ = $this->motionZ;
  $pk->yaw = $this->yaw;
  $pk->pitch = $this->pitch;
  $pk->metadata = $this->dataProperties;
  $player->dataPacket($pk);
  
  ProjectileEntity::spawnTo($player);
 }

}