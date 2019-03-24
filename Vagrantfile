# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu/bionic64"

  config.vm.provider "virtualbox" do |vb|
    vb.cpus = "2"
    vb.memory = "1024"
  end

  config.vm.provision "shell", inline: <<-SHELL
    apt-get update
    apt-get upgrade -y
    apt-get install -y rabbitmq-server
    apt-get install -y redis-server
    apt-get install -y php-cli php-bcmath php-mbstring php-dom php-zip php-amqp php-redis
    apt-get autoremove -y
  SHELL

  config.vm.provision "shell", path: "install-composer.sh"

end
