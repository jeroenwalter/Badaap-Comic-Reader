@echo off

pushd .

call sencha app build package

popd

del build.sqlite

pause
