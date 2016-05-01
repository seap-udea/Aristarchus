DATABASE=Sinfin
USERDB=sinfin
TABLE="Reconocimientos"
BACKDIR="data/dump"

clean:
	touch delete.pyc delete~
	rm -r *.pyc
	find . -name "*~" -exec rm {} \;

cleanrecon:
	@echo -n "Please provide password for user '$(USERDB)': "
	@mysql -u sinfin -p $(DATABASE) -e "truncate table $(TABLE);"
	@rm -rf data/recon/*

cleantrash:
	rm -rf trash/*

cleanall:clean cleanrecon cleantrash

commit:
	@echo "Commiting changes..."
	@-git commit -am "Commit"
	@git push origin master

pull:
	@echo "Pulling from repository..."
	@git reset --hard HEAD	
	@git pull
	@chown -R www-data.www-data .

backup:
	@echo "Backuping sinfin..."
	@bash backup.sh 

restore:
	@echo "Restoring table $TABLE..."
	@-p7zip -d $(BACKDIR)/$(DATABASE).tar.7z
	@-tar xf $(BACKDIR)/$(DATABASE).tar
	@echo -n "Enter root mysql password: "
	@mysql -u root -p $(DATABASE) < $(BACKDIR)/$(DATABASE).sql
	@p7zip $(BACKDIR)/$(DATABASE).tar

permissions:
	@echo "Setting web permissions..."
	@chown -R www-data.www-data .

edit:
	@emacs -nw makefile *.php web/*.php css/*.css js/*.js
