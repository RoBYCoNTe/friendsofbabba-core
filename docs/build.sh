#!/bin/bash

# $1 = root path of the project where plugin is installed
# $2 = path to the documentation
# $3 = version to generate

# Define function that explains usage of the script in case of errors:
explain_usage() {
  echo "Usage: $0 <project path> <output path> <version>"
  echo " <project path> - path to the project where plugin is installed"
  echo " <output path> - path to the documentation"
  echo " <version> - version to generate"
  exit 1
}

# Assign parameters to variables:
CAKEPHP_API_DOCS_PATH="cakephp-api-docs"
PROJECT_PATH=$1
OUTPUT_PATH=$2
VERSION=$3

if [ -z "$PROJECT_PATH" ] || [ ! -d "$PROJECT_PATH" ]; then
  echo "Project path $PROJECT_PATH is not a valid directory"
  explain_usage
fi

# Check if the documentation path is set and exists
if [ -z "$OUTPUT_PATH" ] || [ ! -d "$OUTPUT_PATH" ]; then
  echo "Output path $OUTPUT_PATH is not a valid directory"
  explain_usage
fi

# Check that version is valid and is two numbers separated by a dot
if [[ ! "$VERSION" =~ ^[0-9]+\.[0-9]+$ ]]; then
  echo "The version $VERSION is not valid"
  explain_usage
fi


if [ ! -d $CAKEPHP_API_DOCS_PATH ]
then
	echo "Cloning CakePHP API docs generator into $CAKEPHP_API_DOCS_PATH"
	git clone git@github.com:cakephp/cakephp-api-docs.git $CAKEPHP_API_DOCS_PATH
fi


cp -r config $CAKEPHP_API_DOCS_PATH
cp composer.phar $CAKEPHP_API_DOCS_PATH

cd $CAKEPHP_API_DOCS_PATH
echo "Build docker image..."
docker build -t cakephp/api-docs .

echo "Generating documentation for version $VERSION"
echo "================================================"
echo "Project path: $PROJECT_PATH"
echo "Output path: $OUTPUT_PATH"
echo "Version: $VERSION"
echo "================================================"

docker run -it --rm \
		-v $(pwd)/:/data \
		-v $PROJECT_PATH:/data/project \
		-v $OUTPUT_PATH:/data/output-doc \
		-v empty:/data/project/plugins/FriendsOfBabba/Core/docs \
		cakephp/api-docs php bin/apitool.php generate /data/project /data/output-doc --config core --version $VERSION
		# php composer.phar install --no-interaction && \

echo "Documentation generated in to $DOCUMENTATION_PATH"