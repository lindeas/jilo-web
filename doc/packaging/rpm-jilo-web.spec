Name: jilo-web
Version: 0.2.1
Release: 1%{?dist}
Summary: Jitsi logs web observer

License: GPLv2
URL: https://lindeas.com/jilo
Source0: %{name}-%{version}.tar.gz

%define sourcedir %{_builddir}/%{name}-%{version}

BuildArch: noarch
Requires: (nginx or apache2)
Requires: php
Requires: php-db
Requires: php-sqlite3

%description
PHP web interface to Jilo (JItsi Logs Observer)
To use this you need a webserver with php and sqlite support, and you need a database previously
generated by 'jilo'

%prep
%setup -q

%install
# directories
mkdir -p %{buildroot}/etc/jilo-web
mkdir -p %{buildroot}/usr/share/%{name}
mkdir -p %{buildroot}/usr/share/doc/%{name}
mkdir -p %{buildroot}/usr/share/man/man8

# then copy the files
cp %{sourcedir}/jilo-web.conf.php %{buildroot}/etc/%{name}/jilo-web.conf.php
cp %{sourcedir}/jilo-web.schema %{buildroot}/etc/%{name}/jilo-web.schema
cp %{sourcedir}/public_html/* %{buildroot}/usr/share/%{name}
cp %{sourcedir}/CHANGELOG.md %{buildroot}/usr/share/doc/%{name}/
cp %{sourcedir}/LICENSE %{buildroot}/usr/share/doc/%{name}/
cp %{sourcedir}/README.md %{buildroot}/usr/share/doc/%{name}/
cp %{sourcedir}/TODO.md %{buildroot}/usr/share/doc/%{name}/
cp %{sourcedir}/config.apache %{buildroot}/usr/share/doc/%{name}/
cp %{sourcedir}/config.nginx %{buildroot}/usr/share/doc/%{name}/
cp %{sourcedir}/man-jilo.8 %{buildroot}/usr/share/man/man8/%{name}.8

%files
/etc/jilo-web/jilo-web.conf.php
/etc/jilo-web/jilo-web.schema
/usr/share/doc/%{name}/CHANGELOG.md
/usr/share/doc/%{name}/LICENSE
/usr/share/doc/%{name}/README.md
/usr/share/doc/%{name}/TODO.md
/usr/share/doc/%{name}/config.apache
/usr/share/doc/%{name}/config.nginx
/usr/share/man/man8/%{name}.8.gz

%changelog
* Thu Oct 17 2024  Yasen Pramatarov <yasen@lindeas.com> 0.2.1
- Build of upstream v0.2.1
* Sat Aug 31 2024  Yasen Pramatarov <yasen@lindeas.com> 0.2
- Build of upstream v0.2
* Thu Jul 25 2024  Yasen Pramatarov <yasen@lindeas.com> 0.1.1
- Build of upstream v0.1.1
* Wed Jul 12 2024  Yasen Pramatarov <yasen@lindeas.com> 0.1
- Initial build
